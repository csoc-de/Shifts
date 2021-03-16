<?php


namespace OCA\Shifts\Controller;

use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Service\ShiftsChangeService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ShiftsChangeController extends Controller{
	/** @var ShiftsChangeService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;


	public function __construct(IRequest $request, ShiftsChangeService $service, $userId){
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
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use($id){
			return $this->service->find($id);
		});
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string oldAnalystId
	 * @param string newAnalystId
	 * @param bool $adminApproval
	 * @param string $adminApprovalDate
	 * @param bool $analystApproval
	 * @param string $analystApprovalDate
	 * @param string desc
	 */
	public function create(string $oldAnalystid, string $newAnalystId, bool $adminApproval, string $adminApprovalDate, bool $analystApproval, string $analystApprovalDate, string $desc): DataResponse {
		return new DataResponse($this->service->create($oldAnalystid, $newAnalystId, $adminApproval, $adminApprovalDate, $analystApproval, $analystApprovalDate, $desc));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string oldAnalystId
	 * @param string newAnalystId
	 * @param bool $adminApproval
	 * @param string $adminApprovalDate
	 * @param bool $analystApproval
	 * @param string $analystApprovalDate
	 * @param string desc
	 */
	public function update(int $id,string $oldAnalystid, string $newAnalystId, bool $adminApproval, string $adminApprovalDate, bool $analystApproval, string $analystApprovalDate, string $desc){
		return $this->handleNotFound(function() use ($id, $oldAnalystid, $newAnalystId, $adminApproval, $adminApprovalDate, $analystApproval, $analystApprovalDate, $desc){
			return $this->service->update($id, $adminApproval, $adminApprovalDate, $analystApproval, $analystApprovalDate, $desc);
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
}
