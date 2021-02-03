<?php
namespace OCA\Shifts\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Shifts\Db\Shift;
use OCA\Shifts\Db\ShiftMapper;

class ShiftService {

	/** @var ShiftMapper */
	private $mapper;

	public function __construct(ShiftMapper $mapper){
		$this->mapper = $mapper;
	}

	public function findAll(string $userId){
		return $this->mapper->findAll($userId);
	}

	private function handleException($e){
		if($e instanceof  DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException){
			throw new NotFoundException($e->getMessage());
		}else {
			throw $e;
		}
	}

	public function find(int $id, string $userId){
		try{
			return $this->mapper->find($id, $userId);
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
			$shift = $this->mapper->find($id, $userId);
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
}
