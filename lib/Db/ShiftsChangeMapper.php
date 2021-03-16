<?php

namespace OCA\Shifts\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ShiftsChangeMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'shifts_change', ShiftsChange::class);
	}

	/**
	 * @param int $id
	 * @return Entity|ShiftsChange
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): ShiftsChange {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_change')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}
}
