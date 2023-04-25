<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Service;


use OCA\Shifts\Db\ShiftsCalendarChangeMapper;
use OCA\Shifts\Db\ShiftsCalendarChange;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\Exception;
use Psr\Log\LoggerInterface;
use Sabre\DAV\Exception\Forbidden;

class ShiftsCalendarChangeService {
	/** @var LoggerInterface */
	private LoggerInterface $logger;

	/** @var ShiftsCalendarChangeMapper */
	private $mapper;

	public function __construct(LoggerInterface $logger, ShiftsCalendarChangeMapper $mapper) {
		$this->logger = $logger;

		$this->mapper = $mapper;
	}

	public function find(int $id) {
		try{
			return $this->mapper->find($id);
		} catch(Exception $e){
			$this->handleException($e);
		}
	}

	/**
	 * Find all ShiftsChanges
	 * @return ShiftsCalendarChange[]
	 */
	public function findAll(): array {
		return $this->mapper->findAll();
	}

	/**
	 * Find all ShiftsChanges
	 *
	 * @return ShiftsCalendarChange[]
	 * @throws Exception
	 */
	public function findAllOpen(): array {
		return $this->mapper->findAllOpen();
	}

	/**
	 * @throws PermissionException
	 * @throws NotFoundException
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

	/**
	 * @throws Exception
	 * @throws NotFoundException
	 * @throws PermissionException
	 */
	public function create(int $shiftId, int $shiftTypeId, string $shiftDate, string $oldUserId, string $newUserId, string $action, string $dateChanged, string $adminId, bool $isDone){
		try {
			$shiftsCalendarChange = $this->mapper->findByShiftIdAndType($shiftId, $action);
			return $this->update($shiftsCalendarChange->getId(), $shiftId, $shiftTypeId, $shiftDate, $shiftsCalendarChange->getOldUserId(), $newUserId, $shiftsCalendarChange->getAction(), $dateChanged, $adminId, $isDone);
		} catch (DoesNotExistException $exception) {
			$shiftsCalendarChange = new ShiftsCalendarChange();
			$shiftsCalendarChange->setShiftId($shiftId);
			$shiftsCalendarChange->setShiftTypeId($shiftTypeId);
			$shiftsCalendarChange->setShiftDate($shiftDate);
			$shiftsCalendarChange->setOldUserId($oldUserId);
			$shiftsCalendarChange->setNewUserId($newUserId);
			$shiftsCalendarChange->setAction($action);
			$shiftsCalendarChange->setDateChanged($dateChanged);
			$shiftsCalendarChange->setAdminId($adminId);
			$shiftsCalendarChange->setIsDone($isDone);
			return $this->mapper->insert($shiftsCalendarChange);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function update(int $id, int $shiftId, int $shiftTypeId, string $shiftDate, string $oldUserId, string $newUserId, string $action, string $dateChanged, string $adminId, bool $isDone){
		try{
			$shiftsCalendarChange = $this->mapper->find($id);
			$shiftsCalendarChange->setShiftId($shiftId);
			$shiftsCalendarChange->setShiftTypeId($shiftTypeId);
			$shiftsCalendarChange->setShiftDate($shiftDate);
			$shiftsCalendarChange->setOldUserId($oldUserId);
			$shiftsCalendarChange->setNewUserId($newUserId);
			$shiftsCalendarChange->setAction($action);
			$shiftsCalendarChange->setDateChanged($dateChanged);
			$shiftsCalendarChange->setAdminId($adminId);
			$shiftsCalendarChange->setIsDone($isDone);
			return $this->mapper->update($shiftsCalendarChange);
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
	}

	/**
	 * @throws NotFoundException
	 */
	public function updateDone(int $id, bool $isDone) {
		try {
			$shiftsCalendarChange = $this->mapper->find($id);
			$shiftsCalendarChange->setIsDone($isDone);
			return $this->mapper->update($shiftsCalendarChange);
		} catch(Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id) {
		try {
			$shift = $this->mapper->find($id);
			$this->mapper->delete($shift);
			return $shift;
		} catch(Exception $e) {
			$this->handleException($e);
		}
	}
}
