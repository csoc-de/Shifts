<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

namespace OCA\Shifts\Service;

use DateTime;
use DateInterval;
use DatePeriod;
use Exception;

use OCA\Shifts\Controller\ShiftsCalendarChangeController;
use OCA\Shifts\Db\ShiftsCalendarChange;
use OCA\Shifts\Db\ShiftsCalendarChangeMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Shifts\Db\ShiftsType;
use OCA\Shifts\Db\ShiftsTypeMapper;
use OCA\Shifts\Db\Shift;
use OCA\Shifts\Db\ShiftMapper;

class ShiftsTypeService {

	/** @var ShiftsTypeMapper */
	private $mapper;

	/** @var ShiftMapper */
	private $shiftMapper;

	/** @var ShiftsCalendarChangeMapper */
	private $shiftsCalendarChangeMapper;

	public function __construct(ShiftsTypeMapper $mapper, ShiftMapper $shiftMapper, ShiftsCalendarChangeMapper $shiftsCalendarChangeMapper){
		$this->mapper = $mapper;
		$this->shiftMapper = $shiftMapper;
		$this->shiftsCalendarChangeMapper = $shiftsCalendarChangeMapper;
	}

	public function findAll(): array
	{
		return $this->mapper->findAll();
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

	public function create(string $name, string $desc, string $startTimeStamp, string $stopTimeStamp, string $color,
						   int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule, int $skillGroupId, bool $isWeekly, bool $deleted){
		$shiftsType = new ShiftsType();
		$shiftsType->setName($name);
		$shiftsType->setDesc($desc);
		$shiftsType->setStartTimeStamp($startTimeStamp);
		$shiftsType->setStopTimeStamp($stopTimeStamp);
		$shiftsType->setCalendarColor($color);
		$shiftsType->setMoRule($moRule);
		$shiftsType->setTuRule($tuRule);
		$shiftsType->setWeRule($weRule);
		$shiftsType->setThRule($thRule);
		$shiftsType->setFrRule($frRule);
		$shiftsType->setSaRule($saRule);
		$shiftsType->setSoRule($soRule);
		$shiftsType->setSkillGroupId($skillGroupId);
		$shiftsType->setIsWeekly($isWeekly ?: '0');
		$shiftsType->setDeleted($deleted ?: '0');
		return $this->mapper->insert($shiftsType);
	}

	private function deleteShifts(int $shiftsTypeId, int $countToDelete, string $modifier) {
		if($countToDelete > 0) {
			$start = new DateTime();
			$start->modify($modifier);
			$interval = DateInterval::createFromDateString('7 day');

			$end = date_create(date('Y-m-d'));
			$end = date_add($end, date_interval_create_from_date_string('365 days'));
			$period = new DatePeriod($start, $interval, $end);
			foreach ($period as $dt) {
				$shifts = $this->shiftMapper->findByDateTypeandAssignment($dt->format('Y-m-d'),$shiftsTypeId);
				if (count($shifts) >= $countToDelete) {
					for($i = 0; $i < $countToDelete; $i++) {
						$shifts[$i]->setHasChanged('deleted');
						$this->shiftMapper->update($shifts[$i]);
					}
				}
			}
		}
	}

	public function update(int $id, string $name, string $desc, string $startTimeStamp, string $stopTimeStamp, string $color,
						   int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule, int $skillGroupId, bool $isWeekly, bool $deleted){
		try{
			$shiftsType = $this->mapper->find($id);
			$shiftsType->setName($name);
			$shiftsType->setDesc($desc);
			$shiftsType->setStartTimeStamp($startTimeStamp);
			$shiftsType->setStopTimeStamp($stopTimeStamp);
			$shiftsType->setCalendarColor($color);
			if($shiftsType->getMoRule() != $moRule) {
				$countToDelete = $shiftsType->getMoRule() - $moRule;
				$modifier = 'this monday';
				$this->deleteShifts($id, $countToDelete, $modifier);
				$shiftsType->setMoRule($moRule);
			}
			if($shiftsType->getTuRule() != $tuRule) {
				$countToDelete = $shiftsType->getTuRule() - $tuRule;
				$modifier = 'this tuesday';
				$this->deleteShifts($id, $countToDelete, $modifier);
				$shiftsType->setTuRule($tuRule);
			}
			if($shiftsType->getWeRule() != $weRule) {
				$countToDelete = $shiftsType->getWeRule() - $weRule;
				$modifier = 'this wednesday';
				$this->deleteShifts($id, $countToDelete, $modifier);
				$shiftsType->setWeRule($weRule);
			}
			if($shiftsType->getThRule() != $thRule) {
				$countToDelete = $shiftsType->getThRule() - $thRule;
				$modifier = 'this thursday';
				$this->deleteShifts($id, $countToDelete, $modifier);
				$shiftsType->setThRule($thRule);
			}
			if($shiftsType->getFrRule() != $frRule) {
				$countToDelete = $shiftsType->getFrRule() - $frRule;
				$modifier = 'this friday';
				$this->deleteShifts($id, $countToDelete, $modifier);
				$shiftsType->setFrRule($frRule);
			}
			if($shiftsType->getSaRule() != $saRule) {
				$countToDelete = $shiftsType->getSaRule() - $saRule;
				$modifier = 'this saturday';
				$this->deleteShifts($id, $countToDelete, $modifier);
				$shiftsType->setSaRule($saRule);
			}
			if($shiftsType->getSoRule() != $soRule) {
				$countToDelete = $shiftsType->getSoRule() - $soRule;
				$modifier = 'this sunday';
				$this->deleteShifts($id, $countToDelete, $modifier);
				$shiftsType->setSoRule($soRule);
			}
			$shiftsType->setSkillGroupId($skillGroupId);
			$shiftsType->setIsWeekly($isWeekly ?: '0');
			$shiftsType->setDeleted($deleted ?: '0');
			if($deleted) {
				$shifts = $this->shiftMapper->findByShiftsTypeId($id);
				foreach ( $shifts as $shift) {
					$shiftsCalendarChange = new ShiftsCalendarChange();
					$shiftsCalendarChange->setShiftId($shift->getId());
					$shiftsCalendarChange->setShiftTypeId($shift->getShiftTypeId());
					$shiftsCalendarChange->setShiftDate($shift->getDate());
					$shiftsCalendarChange->setOldUserId($shift->getUserId());
					$shiftsCalendarChange->setNewUserId('-1');
					$shiftsCalendarChange->setAction('unassign');
					$shiftsCalendarChange->setDateChanged('');
					$shiftsCalendarChange->setAdminId('unknown');
					$shiftsCalendarChange->setIsDone('0');
					$this->shiftsCalendarChangeMapper->insert($shiftsCalendarChange);
					$this->shiftMapper->delete($shift);
				}
			}
			return $this->mapper->update($shiftsType);
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
	}

	public function delete(int $id){
		try{
			$shiftsType = $this->mapper->find($id);
			$this->mapper->delete($shiftsType);
			$shifts = $this->shiftMapper->findByShiftsTypeId($id);
			foreach ( $shifts as $shift) {
				$shiftsCalendarChange = new ShiftsCalendarChange();
				$shiftsCalendarChange->setShiftId($shift->getId());
				$shiftsCalendarChange->setShiftTypeId($shift->getShiftTypeId());
				$shiftsCalendarChange->setShiftDate($shift->getDate());
				$shiftsCalendarChange->setOldUserId($shift->getUserId());
				$shiftsCalendarChange->setNewUserId('-1');
				$shiftsCalendarChange->setAction('unassign');
				$shiftsCalendarChange->setDateChanged('');
				$shiftsCalendarChange->setAdminId('unknown');
				$shiftsCalendarChange->setIsDone('0');
				$this->shiftsCalendarChangeMapper->insert($shiftsCalendarChange);
				$this->shiftMapper->delete($shift);
			}
			return $shiftsType;
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
	}
}
