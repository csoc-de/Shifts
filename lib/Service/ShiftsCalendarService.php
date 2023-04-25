<?php
/*
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Service;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use OC\OCS\Exception;
use OCA\DAV\CalDAV\CalDavBackend;
use OCA\Shifts\Db\Shift;
use OCA\Shifts\Db\ShiftsCalendarChange;
use OCA\Shifts\Db\ShiftsType;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\Shifts\Settings\Settings;

use OCP\Calendar\Exceptions\CalendarException;
use OCP\IUser;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class ShiftsCalendarService {
    /** @var LoggerInterface */
    private LoggerInterface $logger;

	/** @var ShiftService */
	private ShiftService $shiftService;

	/** @var ShiftsTypeService */
	private ShiftsTypeService $shiftsTypeService;

	/** @var IUserManager */
	private IUserManager $userManager;

	/** @var CalDavBackend */
	private CalDavBackend $calDav;

	/** @var Settings */
	private Settings $settings;


	public function __construct(LoggerInterface $logger, IUserManager $userManager, CalDavBackend $calDav, ShiftService $shiftService, ShiftsTypeService $shiftsTypeService, Settings $settings) {
		$this->logger = $logger;

		$this->calDav = $calDav;
		$this->settings = $settings;
		$this->userManager = $userManager;
		$this->shiftService = $shiftService;
		$this->shiftsTypeService = $shiftsTypeService;
	}

	/**
	 * @throws NotFoundException
	 * @throws PermissionException
	 */
	private function handleException($e) {
		if($e instanceof DoesNotExistException || $e instanceof MultipleObjectsReturnedException) {
			throw new NotFoundException($e->getMessage());
		} else if($e instanceof Forbidden) {
			throw new PermissionException($e->getMessage());
		} else {
			throw $e;
		}
	}

	private function generateUUIDFromShift(Shift $shift, ShiftsType $shiftsType): string {
		$value = $shiftsType->getName() . "_" . DateTimeImmutable::createFromFormat("Y-m-d", $shift->getDate())->format("Ymd") . "_" . strval($shift->getId());

		$hash = hash("SHA3-512", $value);

		return substr($hash, 0, 8) . "-" . substr($hash, 8, 4) . "-" . substr($hash, 12, 4) . "-" . substr($hash, 16, 4) . "-" . substr($hash, 20, 12);
	}

	/**
	 * @throws NotFoundException
	 */
	private function getCalendarObjectByUser(string $username, string $calendarName = "personal"): array {
		$name = strtolower(str_replace(" ", "-", $calendarName));
		$principal = "principals/users/" . $username;
		$calendar = $this->calDav->getCalendarByUri($principal, $name);
		if(is_null($calendar)) {
			$name = strtolower(str_replace(" ", "_", $calendarName));
			$calendar = $this->calDav->getCalendarByUri($principal, $name);
			if(is_null($calendar)) {
				throw new NotFoundException("Could not find calendar with name '" . $name . "' and principal '" . $principal . "'");
			}
		}
		return $calendar;
	}

	/**
	 * @throws NotFoundException
	 */
	private function getCalendarObject(): array {
		return $this->getCalendarObjectByUser($this->settings->getOrganizerName(), $this->settings->getCalendarName());
	}

	/**
	 * @throws NotFoundException
	 * @throws PermissionException
	 */
	private function getShift(int $shiftId): Shift {
		try {
			return $this->shiftService->find($shiftId);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * @throws NotFoundException
	 * @throws PermissionException
	 */
	private function getShiftsType(int $shiftsTypeId): ShiftsType {
		try {
			$shiftsType = $this->shiftsTypeService->find($shiftsTypeId);
			if(is_null($shiftsType)) {
				throw new NotFoundException("Could not find shifts type with id '" . $shiftsTypeId . "'");
			} else {
				return $shiftsType;
			}
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * @throws NotFoundException
	 */
	private function getUser(string $uid): IUser {
		$analyst = $this->userManager->get($uid);
		if(is_null($analyst)) {
			throw new NotFoundException("Could not find analyst with id '" . $uid . "'");
		} else {
			return $analyst;
		}
	}

	private function generateCalendarEvent(Shift $shift, ShiftsType $shiftsType, IUser $analyst): VCalendar {
		$date_start = DateTime::createFromFormat("Y-m-d", $shift->getDate());
		if(!$date_start) {
			throw new RuntimeException("Failed to convert start date of shift '" . $shift->getId() . "': " . $shift->getDate());
		}
		$date_end = DateTime::createFromFormat("Y-m-d", $shift->getDate());
		if(!$date_end) {
			throw new RuntimeException("Failed to convert end date of shift '" . $shift->getId() . "': " . $shift->getDate());
		}

		if(!$shiftsType->isWeekly()) {
			$shiftsType_start = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $shiftsType->getStartTimestamp());
			if(!$shiftsType_start) {
				throw new RuntimeException("Failed to convert start date of shift type '" . $shiftsType->getId() . "': " . $shiftsType->getStartTimestamp());
			}
			$shiftsType_stop = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $shiftsType->getStopTimestamp());
			if(!$shiftsType_stop) {
				throw new RuntimeException("Failed to convert stop date of shift type '" . $shiftsType->getId() . "': " . $shiftsType->getStopTimestamp());
			}

			$date_start = $date_start->setTime((int) $shiftsType_start->format("H"), (int) $shiftsType_start->format("i"),0);
			$date_end = $date_end->setTime((int) $shiftsType_stop->format("H"), (int) $shiftsType_stop->format("i"),0);
			if((int)$date_start->format('H') > (int)$date_end->format('H')) {
				$year = (int) $date_start->format("Y");
				$month = (int) $date_start->format("m");
				$day = (int) $date_start->format("d");
				$date_start = $date_start->setDate($year, $month, $day-1);
			}
		} else {
			$year = (int) $date_start->format("Y");
			$week = (int) $date_start->format("W");
			$date_end = $date_end->setISODate($year, $week, 8)->setTime(0,0,0);
			$date_start = $date_start->setISODate($year, $week, 1)->setTime(0,0,0);
		}

		$date_start = $date_start->setTimezone(new DateTimeZone($this->settings->getShiftsTimezone()));
		$date_end = $date_end->setTimezone(new DateTimeZone($this->settings->getShiftsTimezone()));

		$this->logger->debug("ShiftsCalendarService::generateCalendarEvent()", [
			'shift_date' => $shift->getDate(),
			'shiftsType_start' => $shiftsType->getStartTimeStamp(),
			'shiftsType_stop' => $shiftsType->getStopTimeStamp(),
			'timezone' => $this->settings->getShiftsTimezone(),
			'date_start' => $date_start,
			'date_end' => $date_end,
		]);

		$event = new VCalendar([
			'CALSCALE' => 'GREGORIAN',
			'VERSION' => '2.0',
			'VEVENT' => []
		]);

		$event->VEVENT->add("SUMMARY", $shiftsType->getName() . ": " . $analyst->getDisplayName());
		$event->VEVENT->add("DESCRIPTION", $shiftsType->getDescription());
		$event->VEVENT->add("STATUS", "CONFIRMED");

		if($shiftsType->isWeekly()) {
			$event->VEVENT->add("DTSTART;VALUE=DATE", $date_start->format("Ymd"));
			$event->VEVENT->add("DTEND;VALUE=DATE", $date_end->format("Ymd"));
		} else {
			$event->VEVENT->add("DTSTART;TZID=" . $this->settings->getShiftsTimezone(), $date_start->format("Ymd\THis"));
			$event->VEVENT->add("DTEND;TZID=" . $this->settings->getShiftsTimezone(), $date_end->format("Ymd\THis"));
		}

		$event->VEVENT->add(
			'ORGANIZER',
			'mailto:' . $this->settings->getOrganizerEmail(),
			[
				'CN' => $this->settings->getOrganizerName(),
				'CUTYPE' => 'INDIVIDUAL',
				'PARTSTAT' => 'ACCEPTED'
			]
		);

		$event->VEVENT->add(
			'ATTENDEE',
			'mailto:' . $analyst->getEMailAddress(),
			[
				'CN' => $analyst->getDisplayName(),
				'CUTYPE' => 'INDIVIDUAL',
				'RSVP' => 'TRUE',
				'ROLE' => 'REQ-PARTICIPANT',
				'PARTSTAT' => 'ACCEPTED'
			]
		);

		return $event;
	}

	/**
	 * @throws NotFoundException
	 * @throws PermissionException
	 */
	public function create(int $shiftId): void {
		// Get shift object from service
		$shift = $this->getShift($shiftId);

		// Get shift type object from service
		$shiftsType = $this->getShiftsType($shift->getShiftTypeId());

		// Get user from shift
		$analyst = $this->getUser($shift->getUserId());

		// Get shifts calendar
		$calendar = $this->getCalendarObject();

		// Generate entry id
		$entry_id = $this->generateUUIDFromShift($shift, $shiftsType);

		$event = $this->generateCalendarEvent($shift, $shiftsType, $analyst);

		// Write to shift calendar
		try {
			$this->calDav->createCalendarObject($calendar['id'], $entry_id, $event->serialize());
		} catch (BadRequest $e) {
			$this->handleException($e);
		}

		// Write to shift user calendar
		if($this->settings->getAddUserCalendarEvent()) {
			$calendar = $this->getCalendarObjectByUser($analyst->getUID());
			try {
				$this->calDav->createCalendarObject($calendar['id'], $entry_id, $event->serialize());
			} catch (BadRequest $e) {
				$this->handleException($e);
			}
		}
	}

	/**
	 * @throws PermissionException
	 * @throws NotFoundException
	 */
	public function update(int $shiftId): void {
		// Get shift object from service
		$shift = $this->getShift($shiftId);

		// Get shift type object from service
		$shiftsType = $this->getShiftsType($shift->getShiftTypeId());

		// Get user from shift
		$analyst = $this->getUser($shift->getUserId());

		// Get shifts calendar object
		$calendar = $this->getCalendarObject();

		// Generate entry id
		$entry_id = $this->generateUUIDFromShift($shift, $shiftsType);

		$event = $this->generateCalendarEvent($shift, $shiftsType, $analyst);

		$etag = $this->calDav->updateCalendarObject($calendar['id'], $entry_id, $event->serialize());
		$this->logger->debug("ShiftsCalendarService::update()", ['etag' => $etag]);

		if($this->settings->getAddUserCalendarEvent()) {
			$calendar = $this->getCalendarObjectByUser($analyst->getUID());
			try {
				$etag = $this->calDav->updateCalendarObject($calendar['id'], $entry_id, $event->serialize());
				$this->logger->debug("ShiftsCalendarService::update()", ['etag' => $etag]);
			} catch (CalendarException $e) {

			}
		}
	}

	private function tryUpdateOldCalendarEntries(string $title, $calendarId, $shiftDate, $shiftsType, $updatedEvent, $delete = false): bool {
		// Get all calendar objects from the shifts calendar
		$calObjects = $this->calDav->getCalendarObjects($calendarId);	// Only limited data
		foreach($calObjects as $calObject) {
			// Get whole object
			$obj = $this->calDav->getCalendarObject($calendarId, $calObject['uri']);

			// Parse the caledar data into a VCalendar
			$parsedEvent = Reader::read($obj["calendardata"], Reader::OPTION_FORGIVING);

			// If the SUMMARY matches it is a possible candidate
			if($parsedEvent->VEVENT->SUMMARY != null && $parsedEvent->VEVENT->SUMMARY->getValue() == $title) {
				// Get start time of the calendar event
				$time = null;

				// Construct start timestamp of the shift by combining the start time of the shift type and the date of the shift
				$shift_start = DateTime::createFromFormat("Y-m-d", $shiftDate);
				if(!$shiftsType->isWeekly()) {
					$time = DateTime::createFromFormat("Ymd\THis", $parsedEvent->VEVENT->DTSTART->getValue());
					$shiftsType_start = DateTime::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $shiftsType->getStartTimestamp());
					$shift_start = $shift_start->setTime((int) $shiftsType_start->format("H"), (int) $shiftsType_start->format("i"),0);
					$shift_start = $shift_start->setTimezone(new DateTimeZone($this->settings->getShiftsTimezone()));
					$shift_start = $shift_start->add(\DateInterval::createFromDateString($shift_start->getTimezone()->getOffset($time) . " seconds"));
				} else {
					$time = DateTime::createFromFormat("Ymd", $parsedEvent->VEVENT->DTSTART->getValue());
					$time = $time->setTime(0,0,0);
					$year = (int) $shift_start->format("Y");
					$week = (int) $shift_start->format("W");
					$shift_start = $shift_start->setISODate($year, $week, 1)->setTime(0,0,0);
				}

				// Check if is the same time
				if($time == $shift_start)
				{
					// If it is the same time, we found our calendar event with the same title and timestamp
					// Update calendar event
					if($delete) {
						try {
							$this->calDav->deleteCalendarObject($calendarId, $calObject['uri'], forceDeletePermanently: true);
						} catch (Forbidden $e) {
							$this->logger->error("ShiftsCalendarService::tryUpdateOldCalendarEntries()", ["message" => "Failed to delete calendar entry", "exception" => $e->getMessage()]);
							return false;
						}
						return true;
					} else {
						$this->calDav->updateCalendarObject($calendarId, $calObject['uri'], $updatedEvent->serialize());
						return true;
					}
				}
			} else if($parsedEvent->VEVENT->SUMMARY == null) {
				$this->logger->error("ShiftsCalendarService::tryUpdateOldCalendarEntries()", ["message" => "No summary in event", "exception" => $obj["calendardata"]]);
			}
		}

		return false;
	}

	/**
	 * @throws PermissionException
	 * @throws NotFoundException
	 */
	public function updateByShiftChange(ShiftsCalendarChange $shiftChange): void {
		// Get shift object from service
		$shift = $this->getShift($shiftChange->getShiftId());

		// Get shift type object from service
		$shiftsType = $this->getShiftsType($shift->getShiftTypeId());

		// Get user from shift
		$analyst = $this->getUser($shiftChange->getNewUserId());
		$old_analyst = $this->getUser($shiftChange->getOldUserId());

		// Get shifts calendar object
		$calendar = $this->getCalendarObject();

		// Generate entry id
		$entry_id = $this->generateUUIDFromShift($shift, $shiftsType);

		$event = $this->generateCalendarEvent($shift, $shiftsType, $analyst);

		// Update in shifts calendar
		try {
			// Try update calendar object by our generated uuid
			$this->calDav->updateCalendarObject($calendar['id'], $entry_id, $event->serialize());
		} catch (\Exception $e) {
			// Could not update calendar object. Maybe because it does not exist...

			// Construct title (SUMMARY)
			$title = $shiftsType->getName() . ": " . $old_analyst->getUID();

			if(!$this->tryUpdateOldCalendarEntries($title, $calendar['id'], $shift->getDate(), $shiftsType, $event)) {
				$this->logger->error("ShiftsCalendarService::updateByShiftChange()", ["msg" => "Failed to update shifts calendar", "shift" => $shift, "exception" => $e->getMessage()]);
			}
		}

		if($this->settings->getAddUserCalendarEvent()) {
			$calendar = $this->getCalendarObjectByUser($old_analyst->getUID());
			try {
				// Construct title (SUMMARY)
				$title = $shiftsType->getName() . ": " . $old_analyst->getUID();

				$this->calDav->deleteCalendarObject($calendar['id'], $entry_id, forceDeletePermanently: true);
				if(!$this->tryUpdateOldCalendarEntries($title, $calendar['id'], $shift->getDate(), $shiftsType, $event, true)) {
					$this->logger->error("ShiftsCalendarService::updateByShiftChange()", ["msg" => "Failed to update user calendar", "shift" => $shift]);
				}
			} catch (\Exception $e) {
				$this->logger->error("ShiftsCalendarService::updateByShiftChange()", ["msg" => "Failed to update user calendar" ,"exception" => $e->getMessage()]);
				$this->handleException($e);
			} finally {
				$calendar = $this->getCalendarObjectByUser($analyst->getUID());
				$this->calDav->createCalendarObject($calendar['id'], $entry_id, $event->serialize());
			}
		}
	}

	/**
	 * @throws NotFoundException
	 * @throws PermissionException
	 */
	public function delete(int $shiftId): void {
		// Get shift object from service
		$shift = $this->getShift($shiftId);

		// Get shift type object from service
		$shiftsType = $this->getShiftsType($shift->getShiftTypeId());

		// Get shifts calendar object
		$calendar = $this->getCalendarObject();

		// Generate entry id
		$entry_id = $this->generateUUIDFromShift($shift, $shiftsType);

		try {
			$this->calDav->deleteCalendarObject($calendar['id'], $entry_id, forceDeletePermanently: true);
		} catch (Forbidden | \Exception $e) {
			$this->handleException($e);
		}
	}
}
