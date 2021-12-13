<?php


namespace OCA\Shifts\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ShiftsCalendarChange extends Entity implements JsonSerializable{

	protected $shiftId;
	protected $shiftTypeId;
	protected $shiftDate;
	protected $oldUserId;
	protected $newUserId;
	protected $action;
	protected $dateChanged;
	protected $adminId;
	protected $isDone;

	public function __construct(){
		$this->addType('id','integer');
	}

	public function jsonSerialize(){
		return [
			'id' => $this->id,
			'shiftId' => $this->shiftId,
			'shiftTypeId' => $this->shiftTypeId,
			'shiftDate' => $this->shiftDate,
			'oldUserId' => $this->oldUserId,
			'newUserId' => $this->newUserId,
			'action' => $this->action,
			'dateChanged' => $this->dateChanged,
			'adminId' => $this->adminId,
			'isDone' => $this->isDone,
		];
	}
}
