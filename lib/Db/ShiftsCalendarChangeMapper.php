<?php


namespace OCA\Shifts\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
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
	 * @return array
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
	 * Fetches Shiftchange by shiftId and action
	 * @param int $shiftId
	 * @param string $shiftChangeAction
	 * @return ShiftsCalendarChange
	 */
	public function findByShiftIdAndType(int $shiftId, string $shiftChangeAction): ShiftsCalendarChange {
		error_log($shiftId);
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('shifts_cal_changes')
			->where($qb->expr()->eq('is_done', $qb->createNamedParameter('0')))
			->andWhere($qb->expr()->eq('shift_id', $qb->createNamedParameter($shiftId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('action', $qb->createNamedParameter($shiftChangeAction,  IQueryBuilder::PARAM_STR)))
			->orderBy('shift_type_id');
		return $this->findEntity($qb);
	}
}
