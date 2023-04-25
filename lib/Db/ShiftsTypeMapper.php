<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ShiftsTypeMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'shifts_type', ShiftsType::class);
	}

	/**
	 * Finds ShiftsType by id
	 * @param int $id
	 * @return Entity|ShiftsType
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function find(int $id): ShiftsType {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_type')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * Fetches all ShiftsTypes
	 * @return array
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_type')
			->where($qb->expr()->eq('deleted', $qb->createNamedParameter('0')));
		return $this->findEntities($qb);
	}

	/**
	 * Fetches Shift Rules
	 * @return array
	 */
	public function findAllRules(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('id', 'mo_rule', 'tu_rule', 'we_rule', 'th_rule', 'fr_rule', 'sa_rule', 'so_rule', 'is_weekly')
			->from('shifts_type');
		return $this->findEntities($qb);
	}

	/**
	 * Find all skill group ids
	 * @return array
	 */
	public function findAllSkillGroupIds(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('id', 'skill_group_id')
			->from('shifts_type')
			->groupBy('skill_group_id');
		return $this->findEntities($qb);
	}
}
