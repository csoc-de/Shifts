<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */


namespace OCA\Shifts\Tests\Unit\Service;

use OCA\Shifts\Service\NotFoundException;
use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Db\DoesNotExistException;

use OCA\Shifts\Db\Shift;
use OCA\Shifts\Service\ShiftService;
use OCA\Shifts\Db\ShiftMapper;
use OCA\Shifts\Db\ShiftsTypeMapper;

class ShiftServiceTest extends TestCase
{
	private $service;
	private $mapper;
	private $typeMapper;
	private $userId = 'fabian';

	public function setUp(): void
	{
		$this->mapper = $this->getMockBuilder(ShiftMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->typeMapper = $this->getMockBuilder(ShiftsTypeMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->service = new ShiftService($this->mapper, $this->typeMapper);
	}

	public function testUpdate()
	{
		$shift = new Shift();
		$shift->setUserId('fabian');
		$shift->setShiftTypeId('1');
		$shift->setDate('2021-10-19');
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(0))
			->will($this->returnValue($shift));

		// the note when updated
		$updatedShift = new Shift();
		$updatedShift->setUserId('test');
		$updatedShift->setShiftTypeId('0');
		$updatedShift->setDate('2021-10-20');
		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($updatedShift))
			->will($this->returnValue($updatedShift));

		$result = $this->service->update(0, 'test', '0', '2021-10-20');

		$this->assertEquals($updatedShift, $result);

	}

	public function testUpdateNotFound()
	{
		$this->expectException(NotFoundException::class);
		// test the correct status code if no note is found
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(0))
			->will($this->throwException(new DoesNotExistException('')));

		$this->service->update(0, 'test', '0', '2021-10-20');
	}
}
