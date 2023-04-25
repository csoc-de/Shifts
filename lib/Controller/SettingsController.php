<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Controller;

use \OCP\ILogger;
use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Db\ShiftsTypeMapper;
use OCA\Shifts\Settings\Settings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\AppFramework\Http;
use Psr\Log\LoggerInterface;

class SettingsController extends Controller {
	/** @var LoggerInterface */
	private LoggerInterface $logger;

	/**
	 * Settings
	 *
	 * @var Settings
	 */
	private $settings;

	/** @var ShiftsTypeMapper */
    private $mapper;

	/**
	 * Url generator service
	 *
	 * @var IURLGenerator
	 */
	private $urlGenerator;

	public function __construct(LoggerInterface $logger, IRequest $request, IURLGenerator $urlGenerator, ShiftsTypeMapper $mapper, Settings $settings) {
		parent::__construct(Application::APP_ID, $request);
		$this->logger = $logger;

		$this->urlGenerator = $urlGenerator;
		$this->settings = $settings;
		$this->mapper = $mapper;
	}
	/**
	 * Print Settings section
	 *
	 * @return TemplateResponse
	 */
	public function index(): TemplateResponse {
		$data = $this->getSettings();
		return new TemplateResponse(Application::APP_ID, 'settings', $data, 'blank');
	}

	/**
	 * Saves Settings
	 *
	 * @AdminRequired
	 *
	 * @param string $calendarName
	 * @param boolean $addUserCalendarEvent
	 * @param string $organizerName
	 * @param string $organizerEmail
	 * @param string $adminGroup
	 * @param string $shiftWorkerGroup
	 * @param string $shiftChangeSameShiftType
	 * @param array $skillGroups
	 * @return DataResponse
	 */
	public function saveSettings(string $calendarName, bool $addUserCalendarEvent, string $organizerName, string $organizerEmail, string $timezone, string $adminGroup, string $shiftWorkerGroup, string $shiftChangeSameShiftType, array $skillGroups): DataResponse {
		$skillGroupIds = $this->mapper->findAllSkillGroupIds();
		for($i = 0; $i < count($skillGroupIds); $i++) {
			$skillGroupPresent = false;
			for($j = 0; $j < count($skillGroups); $j++) {
				if($skillGroupIds[$i]->getSkillGroupId() == $skillGroups[$j]['id']) {
					$skillGroupPresent = true;
				}
			}

			if(!$skillGroupPresent) {
				return new DataResponse("Skill group is still in use and cannot be deleted!", Http::STATUS_BAD_REQUEST);
			}
		}

		$this->settings->setCalendarName($calendarName);
		$this->settings->setAddUserCalendarEvent($addUserCalendarEvent);
		$this->settings->setOrganizerName($organizerName);
		$this->settings->setOrganizerEmail($organizerEmail);
		$this->settings->setAdminGroup($adminGroup);
		$this->settings->setShiftsTimezone($timezone);
		$this->settings->setShiftWorkerGroup($shiftWorkerGroup);
		$this->settings->setSkillGroups($skillGroups);
		$this->settings->setShiftChangeSameShiftType($shiftChangeSameShiftType);
		return new DataResponse($this->getSettings());
	}

	/**
	 * Get app settings
	 * @NoAdminRequired
	 *
	 * @return array
	 */
	public function getSettings(): array {
		return [
			'calendarName' => $this->settings->getCalendarName(),
			'addUserCalendarEvent' => $this->settings->getAddUserCalendarEvent(),
			'organizerName' => $this->settings->getOrganizerName(),
			'organizerEmail' => $this->settings->getOrganizerEmail(),
			'timezone' => $this->settings->getShiftsTimezone(),
			'adminGroup' => $this->settings->getAdminGroup(),
			'shiftWorkerGroup' => $this->settings->getShiftWorkerGroup(),
			'skillGroups' => $this->settings->getSkillGroups(),
			'shiftChangeSameShiftType' => $this->settings->getShiftChangeSameShiftType(),
		];
	}
}
