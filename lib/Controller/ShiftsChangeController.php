<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Controller;

use Exception;
use OCA\Shifts\Service\PermissionException;
use OCA\Shifts\Service\InvalidArgumentException;

use OCP\AppFramework\Http;
use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Service\ShiftsChangeService;
use OCA\Shifts\Service\PermissionService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ShiftsChangeController extends Controller{
	/** @var ShiftsChangeService */
	private $service;

	private $permService;

	use Errors;


	public function __construct(IRequest $request, ShiftsChangeService $service, PermissionService $permService){
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->permService = $permService;
	}

	private function handleException(Exception $e): DataResponse {
		if($e instanceof PermissionException) {
			return new DataResponse(NULL, Http::STATUS_UNAUTHORIZED);
		} elseif($e instanceof InvalidArgumentException) {
			return new DataResponse(NULL, Http::STATUS_BAD_REQUEST);
		} elseif($e instanceof NotFoundException) {
			return new DataResponse(NULL, Http::STATUS_NOT_FOUND);
        } else {
			return new DataResponse(NULL, Http::STATUS_INTERNAL_SERVER_ERROR);
		}
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
		try {
			return $this->service->find($id);
		} catch(Exception $e) {
			return $this->handleException($e);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string oldAnalystId
	 * @param string newAnalystId
	 * @param string $adminApproval
	 * @param string $adminApprovalDate
	 * @param string $analystApproval
	 * @param string $analystApprovalDate
	 * @param int $oldShiftsId
	 * @param int $newShiftsId
	 * @param string desc
	 * @param int type
	 * @return DataResponse
	 */
	public function create(string $oldAnalystId, string $newAnalystId, string $adminApproval, string $adminApprovalDate, string $analystApproval, string $analystApprovalDate, int $oldShiftsId, int $newShiftsId, string $desc, int $type): DataResponse {
		if($this->permService->isRequestingUser($oldAnalystId) || $this->permService->isRequestingUserAdmin()) {
			try {
				return new DataResponse($this->service->create($oldAnalystId, $newAnalystId, $adminApproval, $adminApprovalDate, $analystApproval, $analystApprovalDate, $oldShiftsId, $newShiftsId, $desc, $type));
			} catch (Exception $e) {
				return $this->handleException($e);
			}
		} else {
			return new DataResponse(NULL, Http::STATUS_UNAUTHORIZED);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string oldAnalystId
	 * @param string newAnalystId
	 * @param string $adminApproval
	 * @param string $adminApprovalDate
	 * @param string $analystApproval
	 * @param string $analystApprovalDate
	 * @param int $oldShiftsId
	 * @param int $newShiftsId
	 * @param string desc
	 * @param string type
	 * @return DataResponse
	 */
	public function update(int $id, string $oldAnalystId, string $newAnalystId, string $adminApproval, string $adminApprovalDate, string $analystApproval, string $analystApprovalDate, int $oldShiftsId, int $newShiftsId, string $desc, int $type): DataResponse
	{
		if($this->permService->isRequestingUser($oldAnalystId) || $this->permService->isRequestingUser($newAnalystId) || $this->permService->isRequestingUserAdmin()) {
			try {
				$result = $this->service->update($id, $oldAnalystId, $newAnalystId, $adminApproval, $adminApprovalDate, $analystApproval, $analystApprovalDate, $oldShiftsId, $newShiftsId, $desc, $type);
				return new DataResponse($result, Http::STATUS_OK);
			} catch (Exception $e) {
				return $this->handleException($e);
			}
		} else {
			return new DataResponse(NULL, Http::STATUS_UNAUTHORIZED);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return DataResponse
	 */
	public function destroy(int $id): DataResponse
	{
		if($this->permService->isRequestingUserAdmin()) {
			try {
				$result = $this->service->delete($id);
				return new DataResponse($result, Http::STATUS_OK);
			} catch(Exception $e) {
				return $this->handleException($e);
			}
		} else {
			return new DataResponse(NULL, Http::STATUS_UNAUTHORIZED);
		}
	}
}
