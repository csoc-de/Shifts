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

class Shift extends Entity implements JsonSerializable {

	protected $userId;
	protected $shiftTypeId;
	protected $date;

	public function __construct(){
		$this->addType('id','integer');
	}

	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->userId;
	}

	/**
	 * @return int
	 */
	public function getShiftTypeId(): int {
		return $this->shiftTypeId;
	}

	/**
	* @return string
	*/
	public function getDate(): string {
		return $this->date;
	}

	public function jsonSerialize(){
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'shiftTypeId' => $this->shiftTypeId,
			'date' => $this->date,
		];
	}

}
