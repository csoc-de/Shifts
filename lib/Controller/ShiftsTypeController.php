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
	 * @param int $moRule
	 * @param int $tuRule
	 * @param int $weRule
	 * @param int $thRule
	 * @param int $frRule
	 * @param int $saRule
	 * @param int $soRule
	 * @return DataResponse
	 */
	public function create(string $name, string $description, string $startTimestamp, string $stopTimestamp, string $color,
						   int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule): DataResponse {
		return new DataResponse($this->service->create($name, $description, $startTimestamp, $stopTimestamp, $color,
														$moRule, $tuRule, $weRule, $thRule, $frRule, $saRule, $soRule));
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
	 * @return DataResponse
	 */
	public function update(int $id, string $name, string $desc, string $startTimestamp, string $stopTimestamp, string $color,
						   int $moRule, int $tuRule, int $weRule, int $thRule, int $frRule, int $saRule, int $soRule): DataResponse
	{
		return $this->handleNotFound(function() use ($id, $name, $desc, $startTimestamp, $stopTimestamp, $color,
			$moRule, $tuRule, $weRule, $thRule, $frRule, $saRule, $soRule){
			return $this->service->update($id, $name, $desc, $startTimestamp, $stopTimestamp, $color,
											$moRule, $tuRule, $weRule, $thRule, $frRule, $saRule, $soRule);
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
