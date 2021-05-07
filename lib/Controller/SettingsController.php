<?php


namespace OCA\Shifts\Controller;


use OCA\Shifts\AppInfo\Application;
use OCA\Shifts\Settings\Settings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IURLGenerator;

class SettingsController extends Controller {

	/**
	 * Settings
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Url generator service
	 *
	 * @var IURLGenerator
	 */
	private $urlGenerator;

	public function __construct(IRequest $request, IURLGenerator $urlGenerator, Settings $settings) {
		parent::__construct(Application::APP_ID, $request);

		$this->urlGenerator = $urlGenerator;
		$this->settings = $settings;
	}
	/**
	 * Print Settings section
	 *
	 * @return TemplateResponse
	 */
	public function index(): TemplateResponse {
		$data = [
			'calendarName' => $this->settings->getCalendarName(),
			'organizerName' => $this->settings->getOrganizerName(),
			'organizerEmail' => $this->settings->getOrganizerEmail(),
			'adminGroup' => $this->settings->getAdminGroup(),
			'shiftWorkerGroup' => $this->settings->getShiftWorkerGroup(),
			'skillGroups' => $this->settings->getSkillGroups(),
		];
		return new TemplateResponse(Application::APP_ID, 'settings', $data, 'blank');
	}

	/**
	 * Saves Settings
	 *
	 * @param string $calendarName
	 * @param string $organizerName
	 * @param string $organizerEmail
	 * @param string $adminGroup
	 * @param string $shiftWorkerGroup
	 * @param array $skillGroups
	 */
	public function saveSettings(string $calendarName, string $organizerName, string $organizerEmail, string $adminGroup, string $shiftWorkerGroup, array $skillGroups) {
		$this->settings->setCalendarName($calendarName);
		$this->settings->setOrganizerName($organizerName);
		$this->settings->setOrganizerEmail($organizerEmail);
		$this->settings->setAdminGroup($adminGroup);
		$this->settings->setShiftWorkerGroup($shiftWorkerGroup);
		$this->settings->setSkillGroups($skillGroups);
	}

	/**
	 * Get app settings
	 *
	 * @return array
	 *
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function getSettings(): array {
		return [
			'calendarName' => $this->settings->getCalendarName(),
			'organizerName' => $this->settings->getOrganizerName(),
			'organizerEmail' => $this->settings->getOrganizerEmail(),
			'adminGroup' => $this->settings->getAdminGroup(),
			'shiftWorkerGroup' => $this->settings->getShiftWorkerGroup(),
			'skillGroups' => $this->settings->getSkillGroups(),
		];
	}
}
