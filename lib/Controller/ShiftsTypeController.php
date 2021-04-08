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
	 * @return DataResponse
	 */
	public function create(string $name, string $description, string $startTimestamp, string $stopTimestamp, string $color): DataResponse {
		return new DataResponse($this->service->create($name, $description, $startTimestamp, $stopTimestamp, $color));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string $name
	 * @param string $desc
	 * @param string $startTimeStamp
	 * @param string $stopTimeStamp
	 * @param string $color
	 * @return DataResponse
	 */
	public function update(int $id, string $name, string $desc, string $startTimeStamp, string $stopTimeStamp, string $color): DataResponse
	{
		return $this->handleNotFound(function() use ($id, $name, $desc, $startTimeStamp, $stopTimeStamp, $color){
			return $this->service->update($id, $name, $desc, $startTimeStamp, $stopTimeStamp, $color);
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
