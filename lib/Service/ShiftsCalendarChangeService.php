<?php


namespace OCA\Shifts\Service;


use OCA\Shifts\Db\ShiftsCalendarChangeMapper;
use OCA\Shifts\Db\ShiftsCalendarChange;

use OCP\AppFramework\Db\DoesNotExistException;

class ShiftsCalendarChangeService {
	/** @var ShiftsCalendarChangeMapper */
	private $mapper;

	public function __construct(ShiftsCalendarChangeMapper $mapper){
		$this->mapper = $mapper;
	}

	public function find(int $id){
		try{
			return $this->mapper->find($id);
		} catch(Exception $e){
			$this->handleException($e);
		}
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

	public function create(int $shiftId, int $shiftTypeId, string $shiftDate, string $oldUserId, string $newUserId,
						   string $action, string $dateChanged, string $adminId, bool $isDone){
		try {
			error_log($shiftId);
			error_log(gettype($shiftId));
			$shiftsCalendarChange = $this->mapper->findByShiftIdAndType($shiftId, $action);
			return $this->update($shiftsCalendarChange->getId(), $shiftId, $shiftTypeId, $shiftDate, $oldUserId, $newUserId, $action, $dateChanged, $adminId, $isDone);

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
			$shiftsCalendarChange->setIsDone($isDone ?: '0');
			return $this->mapper->insert($shiftsCalendarChange);
		}
	}

	public function update(int $id, int $shiftId, int $shiftTypeId, string $shiftDate, string $oldUserId, string $newUserId,
						   string $action, string $dateChanged, string $adminId, bool $isDone){
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
			$shiftsCalendarChange->setIsDone($isDone ?: '0');
			return $this->mapper->update($shiftsCalendarChange);
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
}
