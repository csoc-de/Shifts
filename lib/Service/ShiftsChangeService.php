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

	public function findAll(){
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

	public function create(bool $adminApproval, string $adminApprovalDate, bool $analystApproval, string $analystApprovalDate, string $desc, string $type){
		$shiftsChange = new ShiftsChange();
		$shiftsChange->setAdminApproval($adminApproval);
		$shiftsChange->setAdminApprovalDate($adminApprovalDate);
		$shiftsChange->setAnalystApproval($analystApproval);
		$shiftsChange->setAnalystApprovalDate($analystApprovalDate);
		$shiftsChange->setDesc($desc);
		$shiftsChange->setType($type);
		return $this->mapper->insert($shiftsChange);
	}

	public function update(int $id, bool $adminApproval, string $adminApprovalDate, bool $analystApproval, string $analystApprovalDate, string $desc, string $type){
		try{
			$shiftsChange = $this->mapper->find($id);
			$shiftsChange->setAdminApproval($adminApproval);
			$shiftsChange->setAdminApprovalDate($adminApprovalDate);
			$shiftsChange->setAnalystApproval($analystApproval);
			$shiftsChange->setAnalystApprovalDate($analystApprovalDate);
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
