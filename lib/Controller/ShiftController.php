<?php


namespace OCA\Shifts\Controller;

use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Service\ShiftService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IGroupManager;

class ShiftController extends Controller{
	/** @var ShiftService */
	private $service;

	/** @var string */
	private $userId;

	/** @var IGroupManager */
	private $groupManager;

	use Errors;


	public function __construct(IRequest $request,IGroupManager $groupManager, ShiftService $service, $userId){
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
		$this->groupManager = $groupManager;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): DataResponse {

		return new DataResponse($this->service->findAll($this->userId));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use($id){
			return $this->service->find($id, $this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string $userId
	 * @param int $shiftTypeId
	 * @param string $date
	 */
	public function create(string $userId, int $shiftTypeId, string $date): DataResponse {
		return new DataResponse($this->service->create($userId, $shiftTypeId, $date));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string $userId
	 * @param int $shiftTypeId
	 * @param string $date
	 */
	public function update(int $id, string $userId, int $shiftTypeId, string $date){
		return $this->handleNotFound(function() use ($id, $userId, $shiftTypeId, $date){
			return $this->service->update($id, $userId, $shiftTypeId, $date);
		});
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 */
	public function destroy(int $id){
		return $this->handleNotFound(function() use($id) {
			return $this->service->delete($id);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function getGroupStatus(){
		return new DataResponse($this->groupManager->isInGroup($this->userId,"ShiftsAdmin"));
	}

	/**
	 * @NoAdminRequired
	 */
	public function getAllAnalysts(){
		$group = $this->groupManager->get('analyst');
		$users = $group->getUsers();
		return new DataResponse($users);
	}
}
