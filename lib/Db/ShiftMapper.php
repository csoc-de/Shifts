<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@outlook.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@outlook.de>
 */

namespace OCA\Shifts\Db;

use DateTime;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ShiftMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'shifts', Shift::class);
	}

	/**
	 * Finds Shift by Shiftid
	 * @param int $id
	 * @return Entity|Shift
	 * @throws MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): Shift {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * Finds all Shifts by given userId
	 * @param string $userId
	 * @return array
	 */
	public function findById(string $userId): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntities($qb);
	}
	/**
	 * Fetches all Shifts
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts');
		return $this->findEntities($qb);
	}

	/**
	 * Fetches shifts by Datestring
	 * @return array
	 */
	public function findByDateAndType($currentDate, $type): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts')
			->where($qb->expr()->eq('date', $qb->createNamedParameter($currentDate)))
			->andWhere($qb->expr()->eq('shift_type_id', $qb->createNamedParameter($type)));
		return $this->findEntities($qb);
	}

	/**
	 * Fetches all assigned Shifts
	 * @return array
	 */
	public function findAssignedShifts() : array {
		/* @var $qb IQueryBuilder */
		$currentDate = date('Y-m-d', strtotime("-7 days"));
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts')
			->where($qb->expr()->neq('user_id', $qb->createNamedParameter('-1')))
			->andWhere($qb->expr()->gte('date', $qb->createNamedParameter($currentDate)))
			->orderBy('date');
		return $this->findEntities($qb);
	}

	/**
	 * Fetches all shifts in timerange
	 * @return array
	 */
	public function findByTimeRange(string $start, string $end) : array {
		/* @var $qb IQueryBuilder */
		$startDate = date('Y-m-d', strtotime($start));
		$endDate = date('Y-m-d', strtotime($end));
		error_log($startDate);
		error_log($endDate);
		$qb = $this->db->getQueryBuilder();
		$qb->select('user_id', 'shift_type_id', $qb->func()->count('*','num_shifts'))
			->from('shifts')
			->where($qb->expr()->neq('user_id', $qb->createNamedParameter('-1')))
			->andWhere($qb->expr()->gte('date', $qb->createNamedParameter($startDate)))
			->andWhere($qb->expr()->lt('date', $qb->createNamedParameter($endDate)))
			->groupBy('user_id', 'shift_type_id')
			->orderBy('user_id');
		$result = $qb->execute();
		return $result->fetchAll();
	}
}
