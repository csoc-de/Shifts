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

class ShiftsChange extends Entity implements JsonSerializable {

	protected $oldAnalystId;
	protected $newAnalystId;
	protected $adminApproval;
	protected $adminApprovalDate;
	protected $analystApproval;
	protected $analystApprovalDate;
	protected $oldShiftsId;
	protected $newShiftsId;
	protected $desc;
	protected $type;

	public function __construct(){
		$this->addType('id','integer');
	}

	public function getOldShiftsId(): int {
		return $this->oldShiftsId;
	}

	public function getNewShiftsId(): int {
		return $this->newShiftsId;
	}

	public function getOldAnalystId(): string {
		return $this->oldAnalystId;
	}

	public function getNewAnalystId(): string {
		return $this->newAnalystId;
	}

	public function jsonSerialize(){
		return [
			'id' => $this->id,
			'oldAnalystId' => $this->oldAnalystId,
			'newAnalystId' => $this->newAnalystId,
			'adminApproval' => $this->adminApproval,
			'adminApprovalDate' => $this->adminApprovalDate,
			'analystApproval' => $this->analystApproval,
			'analystApprovalDate' => $this->analystApprovalDate,
			'oldShiftsId' => $this->oldShiftsId,
			'newShiftsId' => $this->newShiftsId,
			'desc' => $this->desc,
			'type' => $this->type,
		];
	}

}
