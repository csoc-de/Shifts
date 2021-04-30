<?php

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
	 * Fetches last Shift by Date
	 * @return int
	 */
	public function findLastDate(): ?int {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('date')
			->from('shifts')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter('-1')));
		$dates = $this->findEntities($qb);

		$last = 0;
		foreach($dates as $date){
			$test = $date->slugify('date');
			$curDate = strtotime($test);
			if ($curDate > $last) {
				$last = $curDate;
			}
		}
		if ($last === 0){
			return null;
		}
		return $last;
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
}
