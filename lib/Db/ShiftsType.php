<?php


namespace OCA\Shifts\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ShiftsType extends Entity implements JsonSerializable {

	protected $name;
	protected $desc;
	protected $startTimeStamp;
	protected $stopTimeStamp;

	public function __construct(){
		$this->addType('id','integer');
	}

	public function jsonSerialize(){
		return [
			'id' => $this->id,
			'name' => $this->name,
			'desc' => $this->desc,
			'startTimeStamp' => $this->startTimeStamp,
			'stopTimeStamp' => $this->stopTimeStamp
		];
	}

}
