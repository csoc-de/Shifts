<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ShiftsCalendarChange extends Entity implements JsonSerializable {
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

	public function getShiftId(): int {
		return $this->shiftId;
	}

	public function getShiftTypeId(): int {
		return $this->shiftTypeId;
	}

	public function setIsDone(bool $isDone) {
		$this->setter('isDone', $isDone ? '1' : '0');
	}

	public function getAction(): string {
		return $this->action;
	}

	public function getOldUserId(): string {
		return $this->oldUserId;
	}

	public function getNewUserId(): string {
		return $this->newUserId;
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
