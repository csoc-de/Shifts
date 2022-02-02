<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
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

	public function jsonSerialize(){
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'shiftTypeId' => $this->shiftTypeId,
			'date' => $this->date,
		];
	}

}
