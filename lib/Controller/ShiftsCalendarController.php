<?php
/*
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */


namespace OCA\Shifts\Controller;

use Exception;
use OC\Security\CSP\ContentSecurityPolicy;
use OCA\Shifts\Service\NotFoundException;
use OCA\Shifts\Service\PermissionException;
use OCA\Shifts\Service\ShiftsCalendarChangeService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\NotFoundResponse;

use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Service\PermissionService;
use OCA\Shifts\Service\ShiftsCalendarService;
use Psr\Log\LoggerInterface;


class ShiftsCalendarController extends Controller {
	/** @var LoggerInterface */
	private LoggerInterface $logger;

	/** @var ShiftsCalendarService */
	private ShiftsCalendarService $service;

	/** @var ShiftsCalendarChangeService */
	private ShiftsCalendarChangeService $changeService;

	/** @var PermissionService */
	private PermissionService $permService;

	use Errors;


	public function __construct(LoggerInterface $logger, IRequest $request, ShiftsCalendarService $service, ShiftsCalendarChangeService $changeService, PermissionService $permService) {
		parent::__construct(Application::APP_ID, $request);
		$this->logger = $logger;

		$this->service = $service;
		$this->permService = $permService;
		$this->changeService = $changeService;
	}

	private function handleException(Exception $e): Response {
		if($e instanceof NotFoundException) {
			return new NotFoundResponse();
		} else if($e instanceof PermissionException) {
			$resp = new TemplateResponse('shifts', '401', [], 'guest');
			$resp->setContentSecurityPolicy(new ContentSecurityPolicy());
			$resp->setStatus(Http::STATUS_UNAUTHORIZED);
			return $resp;
		} else {
			throw $e;
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @throws Exception
	 */
	public function index(): Response {
		if(!$this->permService->isRequestingUserAdmin()) {
			$this->handleException(new PermissionException());
		}

		return new Response();
	}
	/**
	 * @NoAdminRequired
	 *
	 * @param int $shiftId
	 * @return Response
	 * @throws Exception
	 */
	public function create(int $shiftId): Response {
		if(!$this->permService->isRequestingUserAdmin()) {
			$this->handleException(new PermissionException());
		}

		try {
			$this->service->create($shiftId);
		} catch (Exception $e) {
			return $this->handleException($e);
		}

		return new Response();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $shiftId
	 * @return Response
	 * @throws Exception
	 */
	public function update(int $shiftId): Response {
		if(!$this->permService->isRequestingUserAdmin()) {
			$this->handleException(new PermissionException());
		}

		try {
			$this->service->update($shiftId);
		} catch (Exception $e) {
			return $this->handleException($e);
		}

		return new Response();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $shiftId
	 * @return Response
	 * @throws Exception
	 */
	public function destroy(int $shiftId): Response {
		if(!$this->permService->isRequestingUserAdmin()) {
			$this->handleException(new PermissionException());
		}

		try {
			$this->service->delete($shiftId);
		} catch (Exception $e) {
			return $this->handleException($e);
		}

		return new Response();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return Response
	 * @throws Exception
	 */
	public function synchronize(): Response {
		if(!$this->permService->isRequestingUserAdmin()) {
			$this->handleException(new PermissionException());
		}

		$errors = array();
		$openChanges = $this->changeService->findAllOpen();
		foreach($openChanges as $change) {
			$action = $change->getAction();
			$this->logger->debug("ShiftsCalendarController::synchronize()", ['openChange' => $change]);

			try {
				if($action == "assign") {
					$this->logger->debug("ShiftsCalendarController::synchronize()", ['action' => 'assign']);
					$this->service->create($change->getShiftId());
				} else if($action == "update") {
					$this->logger->debug("ShiftsCalendarController::synchronize()", ['action' => 'update']);
					$this->service->updateByShiftChange($change);
				} else if($action == "unassign") {
					$this->logger->debug("ShiftsCalendarController::synchronize()", ['action' => 'unassign']);
					$this->service->delete($change->getShiftId());
				} else {
					$this->logger->warning("Unknown shift change action type '" . $action . "'");
					continue;
				}

				$this->changeService->updateDone($change->getId(), true);
			} catch(NotFoundException $e) {
				array_push($errors, $e->getMessage());
			} catch (Exception $e) {
				return $this->handleException($e);
			}
		}

		if(sizeof($errors) != 0) {
			$resp = new DataResponse();
			$resp->setContentSecurityPolicy(new ContentSecurityPolicy());
			$resp->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
			$resp->setData($errors);
			return $resp;
		} else {
			return new Response();
		}
	}
}
