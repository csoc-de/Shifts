<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@outlook.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@outlook.de>
 */

namespace OCA\Shifts\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Shifts\Db\ShiftsType;
use OCA\Shifts\Db\ShiftsTypeMapper;

class ShiftsTypeService {

	/** @var ShiftsTypeMapper */
	private $mapper;

	public function __construct(ShiftsTypeMapper $mapper){
		$this->mapper = $mapper;
	}

	public function findAll(): array
	{
		return $this->mapper->findAll();
	}

	private function handleException($e){
		if($e instanceof  DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException){
			throw new NotFoundException($e->getMessage());
		}else {
			throw $e;
		}
	}

	public function find(int $id){
		try{
			return $this->mapper->find($id);
		} catch(Exception $e){
			$this->handleException($e);
		}
	}

	public function create(string $name, string $desc, string $startTimeStamp, string $stopTimeStamp, string $color,
						   int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule, int $skillGroupId, bool $isWeekly){
		$shiftsType = new ShiftsType();
		$shiftsType->setName($name);
		$shiftsType->setDesc($desc);
		$shiftsType->setStartTimeStamp($startTimeStamp);
		$shiftsType->setStopTimeStamp($stopTimeStamp);
		$shiftsType->setCalendarColor($color);
		$shiftsType->setMoRule($moRule);
		$shiftsType->setTuRule($tuRule);
		$shiftsType->setWeRule($weRule);
		$shiftsType->setThRule($thRule);
		$shiftsType->setFrRule($frRule);
		$shiftsType->setSaRule($saRule);
		$shiftsType->setSoRule($soRule);
		$shiftsType->setSkillGroupId($skillGroupId);
		$shiftsType->setIsWeekly($isWeekly ?: '0');
		return $this->mapper->insert($shiftsType);
	}

	public function update(int $id, string $name, string $desc, string $startTimeStamp, string $stopTimeStamp, string $color,
						   int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule, int $skillGroupId, bool $isWeekly){
		try{
			$shiftsType = $this->mapper->find($id);
			$shiftsType->setName($name);
			$shiftsType->setDesc($desc);
			$shiftsType->setStartTimeStamp($startTimeStamp);
			$shiftsType->setStopTimeStamp($stopTimeStamp);
			$shiftsType->setCalendarColor($color);
			$shiftsType->setMoRule($moRule);
			$shiftsType->setTuRule($tuRule);
			$shiftsType->setWeRule($weRule);
			$shiftsType->setThRule($thRule);
			$shiftsType->setFrRule($frRule);
			$shiftsType->setSaRule($saRule);
			$shiftsType->setSoRule($soRule);
			$shiftsType->setSkillGroupId($skillGroupId);
			$shiftsType->setIsWeekly($isWeekly ?: '0');
			return $this->mapper->update($shiftsType);
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
	}

	public function delete(int $id){
		try{
			$shiftsType = $this->mapper->find($id);
			$this->mapper->delete($shiftsType);
			return $shiftsType;
		} catch(Exception $e){
			$this->handleException($e);
		}
		return null;
	}
}
