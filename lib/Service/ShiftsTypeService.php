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
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;

use OCA\Shifts\Db\ShiftsCalendarChange;
use OCA\Shifts\Db\ShiftsCalendarChangeMapper;
use OCA\Shifts\Settings\Settings;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Shifts\Db\ShiftsType;
use OCA\Shifts\Db\ShiftsTypeMapper;
use OCA\Shifts\Db\ShiftMapper;
use Psr\Log\LoggerInterface;

class ShiftsTypeService {

	/** @var ShiftsTypeMapper */
	private $mapper;

	/** @var ShiftMapper */
	private $shiftMapper;

	/** @var ShiftsCalendarChangeMapper */
	private $shiftsCalendarChangeMapper;

	/** @var LoggerInterface */
	private LoggerInterface $logger;

	/** @var Settings */
	private Settings $settings;

	public function __construct(ShiftsTypeMapper $mapper, ShiftMapper $shiftMapper, ShiftsCalendarChangeMapper $shiftsCalendarChangeMapper, Settings $settings, LoggerInterface $logger){
		$this->mapper = $mapper;
		$this->logger = $logger;
		$this->settings = $settings;
		$this->shiftMapper = $shiftMapper;
		$this->shiftsCalendarChangeMapper = $shiftsCalendarChangeMapper;
	}

	private function handleException($e){
		if($e instanceof  DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException){
			throw new NotFoundException($e->getMessage());
		}else {
			throw $e;
		}
	}

	private function validateDate($shiftsType): bool {
		$result = true;

		if($shiftsType->getIsWeekly())
		{
			return $result;
		}

		$ts = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $shiftsType->getStartTimeStamp());
		if(!$ts) {
			$this->logger->info("ShiftsTypeService::validateDate() -> Start-DateTime format is NOT ok", ['timestamp' => $shiftsType->getStartTimeStamp(), 'timezone' => $this->settings->getShiftsTimezone()]);
			$timestamp = DateTime::createFromFormat("H:i", $shiftsType->getStartTimeStamp());
			$timezone = new DateTimeZone($this->settings->getShiftsTimezone());
			$timestamp = $timestamp->sub(DateInterval::createFromDateString($timezone->getOffset($timestamp) . " seconds"));
			$shiftsType->setStartTimeStamp($timestamp->format(DateTimeInterface::RFC3339_EXTENDED));
			$this->logger->info("ShiftsTypeService::validateDate() -> Start-Timestamp converted", ['timestamp' => $shiftsType->getStartTimeStamp(), 'offset' => $timezone->getOffset($timestamp)]);
			$result = false;
		}

		$ts = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $shiftsType->getStopTimeStamp());
		if(!$ts) {
			$this->logger->info("ShiftsTypeService::validateDate() -> Stop-DateTime format is NOT ok", ['timestamp' => $shiftsType->getStopTimeStamp()]);
			$timestamp = DateTime::createFromFormat("H:i", $shiftsType->getStopTimeStamp());
			$timezone = new DateTimeZone($this->settings->getShiftsTimezone());
			$timestamp = $timestamp->sub(DateInterval::createFromDateString($timezone->getOffset($timestamp) . " seconds"));
			$shiftsType->setStopTimeStamp($timestamp->format(DateTimeInterface::RFC3339_EXTENDED));
			$this->logger->info("ShiftsTypeService::validateDate() -> Stop-Timestamp converted", ['timestamp' => $shiftsType->getStopTimeStamp(), 'offset' => $timezone->getOffset($timestamp)]);
			$result = false;
		}

		return $result;
	}

	public function find(int $id) {
		try {
			$shiftsType = $this->mapper->find($id);

			if($this->validateDate($shiftsType)) {
				$this->logger->debug("ShiftsTypeService::find() -> DateTime format is ok", ['shiftTypeId' => $id, 'start timestamp' => $shiftsType->getStartTimestamp(), 'end timestamp' => $shiftsType->getStartTimestamp()]);
			} else {
				$this->mapper->update($shiftsType);
			}

			return $shiftsType;
		} catch(Exception $e) {
			$this->handleException($e);
		}
	}

	public function findAll(): array
	{
		$this->logger->debug("ShiftsTypeService::findAll()");
		try {
			$shiftsTypes = $this->mapper->findAll();

			foreach($shiftsTypes as $shiftsType) {
				if($this->validateDate($shiftsType)) {
					$this->logger->debug("ShiftsTypeService::findAll() -> DateTime format is ok", ['shiftTypeId' => $shiftsType->getid(), 'start timestamp' => $shiftsType->getStartTimeStamp(), 'end timestamp' => $shiftsType->getStartTimeStamp()]);
				} else {
					$this->mapper->update($shiftsType);
				}
			}

			return $shiftsTypes;
		} catch(Exception $e) {
			$this->handleException($e);
		}
	}

	public function create(string $name, string $desc, string $startTimeStamp, string $stopTimeStamp, string $color, int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule, int $skillGroupId, bool $isWeekly) {
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
		$shiftsType->setDeleted('0');
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
						$this->shiftMapper->delete($shifts[$i]);
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
