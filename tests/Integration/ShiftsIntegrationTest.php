<?php
namespace OCA\Shifts\Tests\Integration\Controller;

use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\App;
use OCP\IRequest;

use PHPUnit\Framework\TestCase;

use OCA\Shifts\Db\Shift;
use OCA\Shifts\Db\ShiftsType;


class ShiftsIntegrationTest extends TestCase{
	private $service;
	private $typeMapper;
	private $mapper;
	private $userId = 'fabian';

	public function setUp(): void {
		parent::setUp();
		$app = new App('shifts');
		$container = $app->getContainer();

		$container->registerService('UserId', function($c) {
			return $this->userId;
		});

		$this->service = $container->query(
			'OCA\Shifts\Service\ShiftService'
		);

		$this->typeMapper = $container->query(
			'OCA\Shifts\Db\ShiftsTypeMapper'
		);

		$this->mapper = $container->query(
			'OCA\Shifts\Db\ShiftMapper'
		);
	}

	public function testGetShiftsByUserId() {
		$shift = new Shift();
		$shift->setUserId('fabian');
		$shift->setShiftTypeId('1');
		$shift->setDate('1970-01-01');

		$resultShift = $this->mapper->insert($shift);

		$shiftList = $this->service->findById($this->userId);

		$shiftsIdList = array_column($shiftList, 'id');

		$this->assertContains($resultShift->getId(), $shiftsIdList);

		$this->mapper->delete($resultShift);
	}

	public function testFindInTimeRange() {
		$shiftIn = new Shift();
		$shiftIn->setUserId('fabian');
		$shiftIn->setShiftTypeId('1');
		$shiftIn->setDate('1971-01-01');

		$shiftOut = new Shift();
		$shiftOut->setUserId('-1');
		$shiftOut->setShiftTypeId('1');
		$shiftOut->setDate('1970-01-01');

		$resultIn = $this->mapper->insert($shiftIn);
		$resultOut = $this->mapper->insert($shiftOut);

		$result = $this->service->findByTimeRange('1970-12-31', '1971-01-02');

		$this->assertCount(1, $result);

		$this->mapper->delete($resultIn);
		$this->mapper->delete($resultOut);
	}

	public function testTriggerUnassignedShifts() {
		$shiftsType = new ShiftsType();
		$shiftsType->setName('testing');
		$shiftsType->setDesc('');
		$shiftsType->setStartTimeStamp('00:00');
		$shiftsType->setStopTimeStamp('00:01');
		$shiftsType->setCalendarColor('#ffffffff');
		$shiftsType->setMoRule('1');
		$shiftsType->setTuRule('0');
		$shiftsType->setWeRule('0');
		$shiftsType->setThRule('0');
		$shiftsType->setFrRule('0');
		$shiftsType->setSaRule('0');
		$shiftsType->setSoRule('0');
		$shiftsType->setSkillGroupId('0');
		$shiftsType->setIsWeekly('1');

		$resultShiftsType = $this->typeMapper->insert($shiftsType);

		$this->service->triggerUnassignedShifts();

		$resultShifts = $this->mapper->findByShiftsTypeId($resultShiftsType->getId());

		$this->assertTrue(count($resultShifts) > 0);
		foreach ($resultShifts as $values) {
			$this->assertTrue(date('D', strtotime($values->getDate())) === 'Mon');
			$this->assertEquals($values->getShiftTypeId(), $resultShiftsType->getId());
			$this->assertEquals($values->getUserId(), '-1');
			$this->mapper->delete($values);
		}

		$this->typeMapper->delete($resultShiftsType);
	}
}
