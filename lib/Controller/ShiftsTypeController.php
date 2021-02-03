<?php


namespace OCA\Shifts\Controller;

use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Service\ShiftsTypeService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ShiftsTypeController extends Controller{
	/** @var ShiftsTypeService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;


	public function __construct(IRequest $request, ShiftsTypeService $service, $userId){
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
	 * @param string $name
	 * @param string desc
	 * @param string startTimeStamp
	 * @param string stopTimeStamp
	 */
	public function create(string $name, string $desc, string $startTimeStamp, string $stopTimeStamp): DataResponse {
		return new DataResponse($this->service->create($name, $desc, $startTimeStamp, $stopTimeStamp));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string $name
	 * @param string desc
	 * @param string startTimeStamp
	 * @param string stopTimeStamp
	 */
	public function update(int $id, string $name, string $desc, string $startTimeStamp, string $stopTimeStamp){
		return $this->handleNotFound(function() use ($id, $name, $desc, $startTimeStamp, $stopTimeStamp){
			return $this->service->update($id, $name, $desc, $startTimeStamp, $stopTimeStamp);
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
