<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Shifts\Db\ShiftsChange;
use OCA\Shifts\Db\ShiftsChangeMapper;

class ShiftsChangeService {

	/** @var ShiftsService */
	private $shiftService;

	/** @var PermissionService */
    private $permService;

	/** @var ShiftsCalendarChangeService */
	private $calendarChangeService;

	/** @var ShiftsChangeMapper */
	private $mapper;

	public function __construct(ShiftsChangeMapper $mapper, ShiftService $shiftService, ShiftsCalendarChangeService $calendarChangeService, PermissionService $permService){
		$this->mapper = $mapper;
		$this->shiftService = $shiftService;

		$this->permService = $permService;
		$this->calendarChangeService = $calendarChangeService;
	}

	public function findAll(): array{
		return $this->mapper->findAll();
	}

	public function findAllByUserId(string $userId): array{
		return $this->mapper->findByUserId($userId);
	}

	private function handleException($e){
		if($e instanceof  DoesNotExistException || $e instanceof MultipleObjectsReturnedException){
			throw new NotFoundException($e->getMessage());
		} else {
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

	public function create(string $oldAnalystId, string $newAnalystId, string $adminApproval, string $adminApprovalDate, string $analystApproval, string $analystApprovalDate, string $oldShiftsId, string $newShiftsId, string $desc, int $type){
		if($type < 0 && $type > 1) {
			throw new InvalidArgumentException("Unknown shift change type!");
		}

		$shiftsChange = new ShiftsChange();
		$shiftsChange->setOldAnalystId($oldAnalystId);
		$shiftsChange->setNewAnalystId($newAnalystId);
		$shiftsChange->setAdminApproval("0");
		$shiftsChange->setAdminApprovalDate("");
		$shiftsChange->setAnalystApproval("0");
		$shiftsChange->setAnalystApprovalDate("");
		$shiftsChange->setOldShiftsId($oldShiftsId);
		$shiftsChange->setNewShiftsId($newShiftsId);
		$shiftsChange->setDesc($desc);
		$shiftsChange->setType($type);

		return $this->mapper->insert($shiftsChange);
	}

	public function update(int $id, string $oldAnalystId, string $newAnalystId, string $adminApproval, string $adminApprovalDate, string $analystApproval, string $analystApprovalDate, string $oldShiftsId, string $newShiftsId, string $desc, int $type) {
		try {
			$shiftsChange = $this->mapper->find($id);
			$newShift = null;
			if($shiftsChange->getNewShiftsId() >= 0) {
				$newShift = $this->shiftService->find($shiftsChange->getNewShiftsId());
			}
			$oldShift = $this->shiftService->find($shiftsChange->getOldShiftsId());
			$wasApproved = $shiftsChange->getAdminApproval() && $shiftsChange->getAnalystApproval();

			if($shiftsChange->getOldAnalystId() != $oldAnalystId) {
				throw new InvalidArgumentException("Old analyst id changed!");
			} else if($shiftsChange->getNewAnalystId() != $newAnalystId) {
				throw new InvalidArgumentException("New analyst id changed!");
			} else if($shiftsChange->getOldShiftsId() != $oldShiftsId) {
				throw new InvalidArgumentException("Old shift id changed!");
			} else if($shiftsChange->getNewShiftsId() != $newShiftsId) {
				throw new InvalidArgumentException("New shift id changed!");
			} else if($shiftsChange->getType() != $type) {
				throw new InvalidArgumentException("Shift change type changed!");
			}

			if(!($this->permService->isRequestingUser($newAnalystId) || $this->permService->isRequestingUserAdmin())) {
				if($shiftsChange->getAnalystApproval() != $analystApproval) {
					throw new PermissionException();
				}
			} else if(!$this->permService->isRequestingUserAdmin()) {
				if($shiftsChange->getAdminApproval() != $adminApproval) {
					throw new PermissionException();
				}
			}

			$shiftsChange->setOldAnalystId($oldAnalystId);
			$shiftsChange->setNewAnalystId($newAnalystId);
			$shiftsChange->setAdminApproval($adminApproval);
			$shiftsChange->setAdminApprovalDate($adminApprovalDate);
			$shiftsChange->setAnalystApproval($analystApproval);
			$shiftsChange->setAnalystApprovalDate($analystApprovalDate);
			$shiftsChange->setOldShiftsId($oldShiftsId);
			$shiftsChange->setNewShiftsId($newShiftsId);
			$shiftsChange->setDesc($desc);
			$shiftsChange->setType($type);

			if($type == 0) {
				if(!$wasApproved && $adminApproval && $analystApproval) {
					// Swap shifts if the request was NOT approved before and is now approved
					$this->shiftService->swap($oldShiftsId, $newShiftsId);
					$this->calendarChangeService->create($oldShift->getId(), $oldShift->getShiftTypeId(), $oldShift->getDate(), $oldAnalystId, $newAnalystId, 'update', date(DATE_ATOM), $this->permService->getUserId(), false);
					$this->calendarChangeService->create($newShift->getId(), $newShift->getShiftTypeId(), $newShift->getDate(), $newAnalystId, $oldAnalystId, 'update', date(DATE_ATOM), $this->permService->getUserId(), false);
				} else if($wasApproved && !$adminApproval) {
					// Swap back if the request was approved but the admin revoked his approval
					$this->shiftService->swap($newShiftsId, $oldShiftsId);
					$this->calendarChangeService->create($oldShift->getId(), $oldShift->getShiftTypeId(), $oldShift->getDate(), $newAnalystId, $oldAnalystId, 'update', date(DATE_ATOM), $this->permService->getUserId(), false);
					$this->calendarChangeService->create($newShift->getId(), $newShift->getShiftTypeId(), $newShift->getDate(), $oldAnalystId, $newAnalystId, 'update', date(DATE_ATOM), $this->permService->getUserId(), false);
				}
			} else if($type == 1) {
				if(!$wasApproved && $adminApproval && $analystApproval) {
					// Swap shifts if the request was NOT approved before and is now approved
					$this->shiftService->cede($oldShiftsId, $newAnalystId);
					$this->calendarChangeService->create($oldShift->getId(), $oldShift->getShiftTypeId(), $oldShift->getDate(), $oldAnalystId, $newAnalystId, 'update', date(DATE_ATOM), $this->permService->getUserId(), false);
				} else if($wasApproved && !$adminApproval) {
					// Swap back if the request was approved but the admin revoked his approval
					$this->shiftService->cede($oldShiftsId, $oldAnalystId);
					$this->calendarChangeService->create($oldShift->getId(), $oldShift->getShiftTypeId(), $oldShift->getDate(), $newAnalystId, $oldAnalystId, 'update', date(DATE_ATOM), $this->permService->getUserId(), false);
				}
			} else {
				throw new InvalidArgumentException("Invalid change type!");
			}

			return $this->mapper->update($shiftsChange);
		} catch(PermissionException | InvalidArgumentException $e) {
			throw $e;
		} catch(Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete(int $id){
		try{
			$shiftsChange = $this->mapper->find($id);
			$this->mapper->delete($shiftsChange);
			return $shiftsChange;
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
	}
}
