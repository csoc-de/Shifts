<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Service;

use DateTime;
use DateInterval;
use DatePeriod;
use Exception;

use OCA\Shifts\Db\ShiftsCalendarChangeMapper;
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

	public function findByTimeRange(string $start, string $end) : array
	{
		try {
			return $this->mapper->findByTimeRange($start, $end);
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

	public function find(int $id): Shift {
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
		try{
			$start = new DateTime();
			$start->modify('this monday');
			$interval = DateInterval::createFromDateString('1day');
			$rules = $this->typeMapper->findAllRules();

			$end = date_create(date('Y-m-d'));
			$end = date_add($end, date_interval_create_from_date_string('365 days'));
			$period = new DatePeriod($start, $interval, $end);

			foreach ($period as $dt) {
				foreach ($rules as $rule) {
					$shiftsType = $rule->jsonSerialize();
					$shifts = $this->mapper->findByDateAndType($dt->format('Y-m-d'), $shiftsType['id']);
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
						default:
							$ruleString = 1;
					};
					if (intval($ruleString) > intval(count($shifts))) {
						for ($x = 0; $x < (intval($ruleString) - intval(count($shifts))); $x++) {
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

	public function swap(int $oldId, int $newId) {
		try {
			$oldShift = $this->mapper->find($oldId);
			$newShift = $this->mapper->find($newId);
			$this->mapper->swapShifts($oldId, $oldShift->getUserId(), $oldShift->getDate(), $newId, $newShift->getUserId(), $newShift->getDate());
		} catch(Exception $e) {
			$this->handleException($e);
		}
	}

	public function cede(int $shiftId, string $newAnalystId) {
		try {
			$shift = $this->mapper->find($shiftId);
			$shift->setUserId($newAnalystId);
			return $this->mapper->update($shift);
		} catch(Exception $e) {
			$this->handleException($e);
		}
	}
}
