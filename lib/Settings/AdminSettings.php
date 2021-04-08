<?php

namespace OCA\Shifts\Settings;

use OCA\Shifts\Controller\SettingsController;
use OCA\Shifts\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\BackgroundJob\IJobList;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IL10N;
use OCP\Settings\ISettings;


class AdminSettings implements ISettings {

	/**
	 * Admin constructor
	 */
	public function __construct() {

	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() : TemplateResponse{
		$app = \OC::$server->query(Application::class);
		$container = $app->getContainer();
		return $container->query(SettingsController::class)->index();
	}

	/**
	 * Get section ID
	 *
	 * @return string
	 */
	public function getSection(): string {
		return "shifts";
	}

	/**
	 * Get Priority for Settings Order
	 *
	 * @return int
	 */
	public function getPriority(): int {
		return 50;
	}
}
