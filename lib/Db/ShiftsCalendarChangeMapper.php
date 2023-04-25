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
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ShiftsCalendarChangeMapper  extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'shifts_cal_changes', ShiftsCalendarChange::class);
	}

	public function find(int $id): ShiftsCalendarChange {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_cal_changes')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	/**
	 * Fetches all ShiftsChanges
	 * @return ShiftsCalendarChange[]
	 */
	public function findAll(): array {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_cal_changes')
			->orderBy('shift_type_id');
		return $this->findEntities($qb);
	}

	/**
	 * Fetches all open ShiftsChanges
	 *
	 * @return ShiftsCalendarChange[]
	 * @throws Exception
	 */
	public function findAllOpen(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_cal_changes')
			->where($qb->expr()->eq('is_done', $qb->createNamedParameter('0')))
			->orderBy('shift_type_id', 'DESC');
		return $this->findEntities($qb);
	}

	/**
	 * Fetches all open ShiftsChanges for shiftId
	 *
	 * @return ShiftsCalendarChange[]
	 * @throws Exception
	 */
	public function findAllOpenByShiftId(int $shiftId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_cal_changes')
			->where($qb->expr()->eq('is_done', $qb->createNamedParameter('0')))
			->andWhere($qb->expr()->eq('shift_id', $qb->createNamedParameter($shiftId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}

	/**
	 * Fetches Shiftchange by shiftId and action
	 * @param int $shiftId
	 * @param string $shiftChangeAction
	 * @return ShiftsCalendarChange
	 */
	public function findByShiftIdAndType(int $shiftId, string $shiftChangeAction): ShiftsCalendarChange {
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_cal_changes')
			->where($qb->expr()->eq('is_done', $qb->createNamedParameter('0')))
			->andWhere($qb->expr()->eq('shift_id', $qb->createNamedParameter($shiftId, IQueryBuilder::PARAM_INT)))
			->orderBy('date_changed')
			->setMaxResults(1);
		return $this->findEntity($qb);
	}
}
