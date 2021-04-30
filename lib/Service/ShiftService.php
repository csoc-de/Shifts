<?php
namespace OCA\Shifts\Service;

use DateInterval;
use DatePeriod;
use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Shifts\Db\Shift;
use OCA\Shifts\Db\ShiftMapper;
use OCA\Shifts\Db\ShiftsTypeMapper;
use phpDocumentor\Reflection\Types\Boolean;

class ShiftService {

	/** @var ShiftMapper */
	private $mapper;

	/** @var ShiftsTypeMapper */
	private $typeMapper;

	public function __construct(ShiftMapper $mapper, ShiftsTypeMapper $typeMapper){
		$this->mapper = $mapper;
		$this->typeMapper = $typeMapper;
	}

	public function findAll(): array
	{
		return $this->mapper->findAll();
	}

	public function findById(string $userId): array
	{
		try{
			return $this->mapper->findById($userId);
		} catch(Exception $e){
			$this->handleException($e);
		}
	}
	public function findAssignedShifts() : array
	{
		try {
			return $this->mapper->findAssignedShifts();
		} catch(Exception $e) {
			$this->handleException($e);
		}
	}

	private function handleException($e){
		if($e instanceof  DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException){
			throw new NotFoundException($e->getMessage());
		}else {
			throw $e;
		}
	}

	public function find(int $id){
		try{
			return $this->mapper->find($id);
		} catch(Exception $e){
			$this->handleException($e);
		}
	}

	public function create(string $userId, int $shiftTypeId, string $date){
		$shift = new Shift();
		$shift->setUserId($userId);
		$shift->setShiftTypeId($shiftTypeId);
		$shift->setDate($date);
		return $this->mapper->insert($shift);
	}

	public function update(int $id, string $userId, string $shiftTypeId, string $date){
		try{
			$shift = $this->mapper->find($id);
			$shift->setUserId($userId);
			$shift->setShiftTypeId($shiftTypeId);
			$shift->setDate($date);
			return $this->mapper->update($shift);
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
	}

	public function delete(int $id){
		try{
			$shift = $this->mapper->find($id);
			$this->mapper->delete($shift);
			return $shift;
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
	}

	public function triggerUnassignedShifts(): bool {
		try {
			$start = date_create(date("Y-m-d"));
			$lastDate = $this->mapper->findLastDate();
			$interval = DateInterval::createFromDateString('1day');
			$rules = $this->typeMapper->findAllRules();

			if ($lastDate) {
				$start = date_create();
				$start->setTimestamp($lastDate);
				$start = date_add($start, date_interval_create_from_date_string('1 days'));
			}
			$end = date_create(date('Y-m-d'));
			$end = date_add($end, date_interval_create_from_date_string('365 days'));
			$period = new DatePeriod($start, $interval, $end);

			foreach ($period as $dt) {
				foreach ($rules as $rule) {
					$shiftsType = $rule->jsonSerialize();
					$dayOfWeek = date('w', $dt->getTimestamp());

					switch ($dayOfWeek) {
						case '0':
							$ruleString = $shiftsType['soRule'];
							break;
						case '1':
							$ruleString = $shiftsType['moRule'];
							break;
						case '2':
							$ruleString = $shiftsType['tuRule'];
							break;
						case '3':
							$ruleString = $shiftsType['weRule'];
							break;
						case '4':
							$ruleString = $shiftsType['thRule'];
							break;
						case '5':
							$ruleString = $shiftsType['frRule'];
							break;
						case '6':
							$ruleString = $shiftsType['saRule'];
							break;
					}
					if ($ruleString === '0' || $ruleString === '1') {
						$shift = new Shift();
						$shift->setUserId('-1');
						$shift->setShiftTypeId($shiftsType['id']);
						$shift->setDate($dt->format('Y-m-d'));
						$this->mapper->insert($shift);
					} else if ($ruleString > 1) {
						for ($x = 0; $x < $ruleString; $x++) {
							$shift = new Shift();
							$shift->setUserId('-1');
							$shift->setShiftTypeId($shiftsType['id']);
							$shift->setDate($dt->format('Y-m-d'));
							$this->mapper->insert($shift);
						}
					}
				}
			}
			return true;

		} catch(Exception $e){
			$this->handleException($e);
		}
		return false;
	}
}
