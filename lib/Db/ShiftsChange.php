<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
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
