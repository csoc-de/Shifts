<?php
namespace OCA\Shifts\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Shifts\Db\ShiftsChange;
use OCA\Shifts\Db\ShiftsChangeMapper;

class ShiftsChangeService {

	/** @var ShiftsChangeMapper */
	private $mapper;

	public function __construct(ShiftsChangeMapper $mapper){
		$this->mapper = $mapper;
	}

	public function findAll(): array{
		return $this->mapper->findAll();
	}

	public function findAllByUserId(string $userId): array{
		return $this->mapper->findByUserId($userId);
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

	public function create(string $oldAnalystId, string $newAnalystId, bool $adminApproval, string $adminApprovalDate, bool $analystApproval, string $analystApprovalDate, string $oldShiftsId, string $newShiftsId, string $desc, int $type){
		$shiftsChange = new ShiftsChange();
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
		return $this->mapper->insert($shiftsChange);
	}

	public function update(int $id, string $oldAnalystId, string $newAnalystId, bool $adminApproval, string $adminApprovalDate, bool $analystApproval, string $analystApprovalDate, string $oldShiftsId, string $newShiftsId, string $desc, int $type){
		try{
			$shiftsChange = $this->mapper->find($id);
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
			return $this->mapper->update($shiftsChange);
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
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
