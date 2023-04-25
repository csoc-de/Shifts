<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Controller;

use OCP\AppFramework\Http;
use OCA\Shifts\Service\PermissionService;

use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Service\ShiftsTypeService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ShiftsTypeController extends Controller{
	/** @var ShiftsTypeService */
	private $service;

	/** @var PermissionService */
	private $permService;

	use Errors;


	public function __construct(IRequest $request, ShiftsTypeService $service, PermissionService $permService){
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->permService = $permService;
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
	 * @param string $name
	 * @param string $description
	 * @param string $startTimestamp
	 * @param string $stopTimestamp
	 * @param string $color
	 * @param int $moRule
	 * @param int $tuRule
	 * @param int $weRule
	 * @param int $thRule
	 * @param int $frRule
	 * @param int $saRule
	 * @param int $soRule
	 * @param int $skillGroupId
	 * @param boolean $isWeekly
	 * @param boolean $deleted
	 * @return DataResponse
	 */
	public function create(string $name, string $description, string $startTimestamp, string $stopTimestamp, string $color,
						   int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule, int $skillGroupId, bool $isWeekly): DataResponse {
		if(!$this->permService->isRequestingUserAdmin()) {
			return new DataResponse(NULL, Http::STATUS_UNAUTHORIZED);
		}

		return new DataResponse($this->service->create($name, $description, $startTimestamp, $stopTimestamp, $color,
														$moRule, $tuRule, $weRule, $thRule, $frRule, $saRule, $soRule, $skillGroupId, $isWeekly));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string $name
	 * @param string $desc
	 * @param string $startTimestamp
	 * @param string $stopTimestamp
	 * @param string $color
	 * @param int $moRule
	 * @param int $tuRule
	 * @param int $weRule
	 * @param int $thRule
	 * @param int $frRule
	 * @param int $saRule
	 * @param int $soRule
	 * @param int skillGroupId
	 * @param boolean isWeekly
	 * @param boolean deleted
	 * @return DataResponse
	 */
	public function update(int $id, string $name, string $desc, string $startTimestamp, string $stopTimestamp, string $color,
						   int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule, int $skillGroupId, bool $isWeekly, bool $deleted): DataResponse
	{
		if(!$this->permService->isRequestingUserAdmin()) {
			return new DataResponse(NULL, Http::STATUS_UNAUTHORIZED);
		}

		return $this->handleNotFound(function() use ($id, $name, $desc, $startTimestamp, $stopTimestamp, $color,
			$moRule, $tuRule, $weRule, $thRule, $frRule, $saRule, $soRule, $skillGroupId, $isWeekly, $deleted){
			return $this->service->update($id, $name, $desc, $startTimestamp, $stopTimestamp, $color,
											$moRule, $tuRule, $weRule, $thRule, $frRule, $saRule, $soRule, $skillGroupId, $isWeekly, $deleted);
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
		if(!$this->permService->isRequestingUserAdmin()) {
			return new DataResponse(NULL, Http::STATUS_UNAUTHORIZED);
		}

		return $this->handleNotFound(function() use($id) {
			return $this->service->delete($id);
		});
	}
}
