<?php


namespace OCA\Shifts\Controller;


use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Service\ShiftsCalendarChangeService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ShiftsCalendarChangeController extends Controller
{
	/** @var ShiftsCalendarChangeService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;


	public function __construct(IRequest $request, ShiftsCalendarChangeService $service, $userId){
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): DataResponse {
		return new DataResponse($this->service->findAll());
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return DataResponse
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use($id){
			return $this->service->find($id);
		});
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $shiftId
	 * @param int $shiftTypeId
	 * @param string $shiftDate
	 * @param string $oldUserId
	 * @param string $newUserId
	 * @param string $action
	 * @param string $dateChanged
	 * @param string $adminId
	 * @param bool $isDone
	 * @return DataResponse
	 */
	public function create(int $shiftId, int $shiftTypeId, string $shiftDate, string $oldUserId, string $newUserId,
						   string $action, string $dateChanged, string $adminId, bool $isDone): DataResponse {
		return new DataResponse($this->service->create($shiftId, $shiftTypeId, $shiftDate, $oldUserId, $newUserId, $action, $dateChanged, $adminId, $isDone));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param int $shiftId
	 * @param int $shiftTypeId
	 * @param string $shiftDate
	 * @param string $oldUserId
	 * @param string $newUserId
	 * @param string $action
	 * @param string $dateChanged
	 * @param string $adminId
	 * @param bool $isDone
	 * @return DataResponse
	 */
	public function update(int $id,int $shiftId, int $shiftTypeId, string $shiftDate, string $oldUserId, string $newUserId,
						   string $action, string $dateChanged, string $adminId, bool $isDone):DataResponse
	{
		return $this->handleNotFound(function() use ($id, $shiftId, $shiftTypeId, $shiftDate, $oldUserId, $newUserId, $action, $dateChanged, $adminId, $isDone){
			return $this->service->update($id, $shiftId, $shiftTypeId, $shiftDate, $oldUserId, $newUserId, $action, $dateChanged, $adminId, $isDone);
		});
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return DataResponse
	 */
	public function destroy(int $id): DataResponse
	{
		return $this->handleNotFound(function() use($id) {
			return $this->service->delete($id);
		});
	}
}
