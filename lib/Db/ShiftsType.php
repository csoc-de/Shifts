<?php


namespace OCA\Shifts\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ShiftsType extends Entity implements JsonSerializable {

	protected $name;
	protected $desc;
	protected $startTimeStamp;
	protected $stopTimeStamp;
	protected $calendarColor;
	protected $moRule;
	protected $tuRule;
	protected $weRule;
	protected $thRule;
	protected $frRule;
	protected $saRule;
	protected $soRule;
	protected $skillGroupId;
	protected $isWeekly;

	public function __construct(){
		$this->addType('id','integer');
	}

	public function jsonSerialize(){
		return [
			'id' => $this->id,
			'name' => $this->name,
			'desc' => $this->desc,
			'startTimestamp' => $this->startTimeStamp,
			'stopTimestamp' => $this->stopTimeStamp,
			'color' => $this->calendarColor,
			'moRule' => $this->moRule,
			'tuRule' => $this->tuRule,
			'weRule' => $this->weRule,
			'thRule' => $this->thRule,
			'frRule' => $this->frRule,
			'saRule' => $this->saRule,
			'soRule' => $this->soRule,
			'skillGroupId' => $this->skillGroupId,
			'isWeekly' => $this->isWeekly,
		];
	}

}
