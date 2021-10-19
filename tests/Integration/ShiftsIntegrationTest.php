<?php
namespace OCA\Shifts\Tests\Integration\Controller;

use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\App;
use OCP\IRequest;

use PHPUnit\Framework\TestCase;

use OCA\Shifts\Db\Shift;
use OCA\Shifts\Db\ShiftMapper;
use OCA\Shifts\Controller\ShiftController;


class ShiftsIntegrationTest extends TestCase {
	private $controller;
	private $mapper;
	private $userId = 'fabian';

	public function setUp() {
		parent::setUp();
		$app = new App('shifts');
		$container = $app->getContainer();

		$container->registerService('UserId', function($c) {
			return $this->userId;
		});

		$container->registerService(IRequest::class, function () {
			return $this->createMock(IRequest::class);
		});

		$this->controller = $container->query(
			'OCA\Shifts\Controller\ShiftController'
		);

		$this->mapper = $container->query(
			'OCA\Shifts\Db\ShiftMapper'
		);
	}

	public function testInsert() {
		$shift = new Shift();
		$shift->setUserId($this->userId);
		$shift->setShiftTypeId('1');
		$shift->setDate('2021-10-19');

		$result = $this->mapper->insert($shift);

		$this->assertEquals($shift, $result);

		// clean up
		$this->mapper->delete($result);
	}
}
