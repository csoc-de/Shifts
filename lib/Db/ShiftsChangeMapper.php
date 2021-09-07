<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

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
	 * Finds ShiftsChange by id
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

	/**
	 * Fetches all ShiftsChanges
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_change');
		return $this->findEntities($qb);
	}

	/**
	 * Finds ShiftsChanges by given userId
	 * @param string $userId
	 * @return array
	 */
	public function findByUserId(string $userId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_change')
			->where($qb->expr()->eq('old_analyst_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR )))
			->orWhere($qb->expr()->eq('new_analyst_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR )));

		return $this->findEntities($qb);
	}

}
