<?php
namespace OCA\Shifts\Tests\Integration\Controller;

use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\App;
use OCP\IRequest;

use PHPUnit\Framework\TestCase;

use OCA\Shifts\Db\Shift;
use OCA\Shifts\Db\ShiftsType;


class ShiftsTypeIntegrationTest extends TestCase{
	private $service;
	private $mapper;
	private $shiftsMapper;
	private $userId = 'fabian';

	public function setUp(): void {
		parent::setUp();
		$app = new App('shifts');
		$container = $app->getContainer();

		$container->registerService('UserId', function($c) {
			return $this->userId;
		});

		$this->service = $container->query(
			'OCA\Shifts\Service\ShiftsTypeService'
		);

		$this->shiftsMapper = $container->query(
			'OCA\Shifts\Db\ShiftMapper'
		);

		$this->mapper = $container->query(
			'OCA\Shifts\Db\ShiftsTypeMapper'
		);
	}

	public function testDeleteShiftsType() {
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

		$resultShiftsType = $this->mapper->insert($shiftsType);


		$shiftOut = new Shift();
		$shiftOut->setUserId('-1');
		$shiftOut->setShiftTypeId($resultShiftsType->getId());
		$shiftOut->setDate('2022-01-03');
		$shiftIn = new Shift();
		$shiftIn->setUserId('fabian');
		$shiftIn->setShiftTypeId($resultShiftsType->getId() + 1);
		$shiftIn->setDate('2022-01-10');

		$resultShiftOut = $this->shiftsMapper->insert($shiftOut);
		$resultShiftIn = $this->shiftsMapper->insert($shiftIn);

		$this->service->delete($resultShiftsType->getId());

		$shiftList = $this->shiftsMapper->findAll();

		$shiftsIdList = array_column($shiftList, 'id');

		$this->assertNotContains($resultShiftOut->getId(), $shiftsIdList);
		$this->assertContains($resultShiftIn->getId(), $shiftsIdList);

		$this->shiftsMapper->delete($resultShiftIn);
	}
}
