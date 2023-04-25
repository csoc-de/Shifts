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
	protected $deleted;

	public function __construct(){
		$this->addType('id','integer');
	}

	public function getName(): string {
		return $this->name;
	}

	public function getDescription(): string {
		return $this->desc;
	}

	public function getStartTimestamp(): string {
		return $this->startTimeStamp;
	}

	public function getStopTimestamp(): string {
		return $this->stopTimeStamp;
	}

	public function isWeekly(): bool {
		return $this->isWeekly;
	}

	public function getSkillGroupId() {
		return $this->skillGroupId;
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
			'deleted' => $this->deleted,
		];
	}

}
