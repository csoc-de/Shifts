<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

namespace OCA\Shifts\Settings;

use OCP\IConfig;


class Settings{

	/**
	 * App name
	 *
	 * @var string
	 */
	private $appName;

	/**
	 * Config service
	 *
	 * @var IConfig
	 */
	private $config;

	/**
	 * Calendar Name Key
	 *
	 * @var string
	 */
	private $_calendarName = "calendarName";

	/**
	 * Calendar Organizer Name Key
	 *
	 * @var string
	 */
	private $_organizerName = "organizerName";

	/**
	 * Calendar Organizer Email Key
	 *
	 * @var string
	 */
	private $_organizerEmail = "organizerEmail";

	/**
	 * Admin Group Name Key
	 *
	 * @var string
	 */
	private $_adminGroup = "adminGroup";

	/**
	 * Shift Worker Group Name Key
	 *
	 * @var string
	 */
	private $_shiftWorkerGroup = "shiftWorkerGroup";

	/**
	 * Shift Worker Categories Group Name Key
	 *
	 * @var string
	 */
	private $_skillGroups = "skillGroups";

	/**
	 * gstc license Key
	 *
	 * @var string
	 */
	private $_gstcLicense = "gstcLicense";

	/**
	 * @param string $AppName
	 */
	public function __construct(string $AppName) {

		$this->appName = $AppName;

		$this->config = \OC::$server->getConfig();
	}

	/**
	 * Get value from the system configuration
	 *
	 * @param string $key
	 * @param bool $system
	 *
	 * @return string|null
	 */
	public function getSystemValue(string $key, bool $system = false): ?string {
		if ($system) {
			return $this->config->getSystemValue($key);
		}

		if (!empty($this->config->getSystemValue($this->appName))
			&& array_key_exists($key, $this->config->getSystemValue($this->appName))) {
			return $this->config->getSystemValue($this->appName)[$key];
		}

		return null;
	}

	/**
	 * Saves Calendar Name
	 *
	 * @param string $calendarName
	 */
	public function setCalendarName(string $calendarName) {
		$this->config->setAppValue($this->appName,$this->_calendarName, $calendarName);
	}

	/**
	 * Get Calendar Name
	 *
	 * @return string
	 */
	public function getCalendarName(): string {
		$calendarName = $this->config->getAppValue($this->appName, $this->_calendarName, "");
		if (empty($calendarName)) {
			$calendarName = $this->GetSystemValue($this->_calendarName);
		}
		if (empty($calendarName)) {
			$this->setCalendarName('ShiftsCalendar');
			$calendarName = 'ShiftsCalendar';
		}
		return $calendarName;
	}

	/**
	 * Saves Organizer Name
	 *
	 * @param string $organizerName
	 */
	public function setOrganizerName(string $organizerName) {
		$this->config->setAppValue($this->appName,$this->_organizerName, $organizerName);
	}

	/**
	 * Get Organizer Name
	 *
	 * @return string
	 */
	public function getOrganizerName(): string {
		$organizerName = $this->config->getAppValue($this->appName, $this->_organizerName, "");
		if (empty($organizerName)) {
			$organizerName = $this->GetSystemValue($this->_organizerName);
		}
		if (empty($organizerName)) {
			$this->setOrganizerName('admin');
			$organizerName = 'admin';
		}
		return $organizerName;
	}

	/**
	 * Saves Organizer Email
	 *
	 * @param string $organizerEmail
	 */
	public function setOrganizerEmail(string $organizerEmail) {
		$this->config->setAppValue($this->appName,$this->_organizerEmail, $organizerEmail);
	}

	/**
	 * Get Organizer Email
	 *
	 * @return string
	 */
	public function getOrganizerEmail(): string {
		$organizerEmail = $this->config->getAppValue($this->appName, $this->_organizerEmail, "");
		if (empty($organizerEmail)) {
			$organizerEmail = $this->GetSystemValue($this->_organizerEmail);
		}
		if (empty($organizerEmail)) {
			$this->setOrganizerEmail('admin@test.com');
			$organizerEmail = 'admin@test.com';
		}
		return $organizerEmail;
	}

	/**
	 * Saves Admin Group Name
	 *
	 * @param string $adminGroup
	 */
	public function setAdminGroup(string $adminGroup) {
		$this->config->setAppValue($this->appName,$this->_adminGroup, $adminGroup);
	}

	/**
	 * Get Admin Group Name
	 *
	 * @return string
	 */
	public function getAdminGroup(): string {
		$adminGroup = $this->config->getAppValue($this->appName, $this->_adminGroup, "");
		if (empty($adminGroup)) {
			$adminGroup = $this->GetSystemValue($this->_adminGroup);
		}
		if (empty($adminGroup)) {
			$this->setAdminGroup('ShiftsAdmin');
			$adminGroup = 'ShiftsAdmin';
		}
		return $adminGroup;
	}

	/**
	 * Saves Shift Worker Group Name
	 *
	 * @param string $shiftWorkerGroup
	 */
	public function setShiftWorkerGroup(string $shiftWorkerGroup) {
		$this->config->setAppValue($this->appName,$this->_shiftWorkerGroup, $shiftWorkerGroup);
	}

	/**
	 * Get Shift Worker Group Name
	 *
	 * @return string
	 */
	public function getShiftWorkerGroup(): string {
		$shiftWorkerGroup = $this->config->getAppValue($this->appName, $this->_shiftWorkerGroup, "");
		if (empty($shiftWorkerGroup)) {
			$shiftWorkerGroup = $this->GetSystemValue($this->_shiftWorkerGroup);
		}
		if (empty($shiftWorkerGroup)) {
			$this->setShiftWorkerGroup('Analyst');
			$shiftWorkerGroup = 'Analyst';
		}
		return $shiftWorkerGroup;
	}

	/**
	 * Saves gstc License
	 *
	 * @param string $gstcLicense
	 */
	public function setGstcLicense(string $gstcLicense) {
		$this->config->setAppValue($this->appName,$this->_gstcLicense, $gstcLicense);
	}

	/**
	 * Get gstc License
	 *
	 * @return string
	 */
	public function getGstcLicense(): string {
		$gstcLicense = $this->config->getAppValue($this->appName, $this->_gstcLicense, "");
		if (empty($gstcLicense)) {
			$gstcLicense = $this->GetSystemValue($this->_gstcLicense);
		}
		if (empty($gstcLicense)) {
			$this->setGstcLicense('');
			$gstcLicense = '';
		}
		return $gstcLicense;
	}


	/**
	 * Saves Skill Group Names
	 *
	 * @param array $skillGroups
	 */
	public function setSkillGroups(array $skillGroups) {
		if (!is_array($skillGroups)) {
			$skillGroups = array();
		}
		$value = json_encode($skillGroups);
		error_log($value);
		$this->config->setAppValue($this->appName,$this->_skillGroups, $value);
	}

	/**
	 * Get Skill Group Names
	 *
	 * @return array
	 */
	public function getSkillGroups(): array {
		$skillGroups = $this->config->getAppValue($this->appName, $this->_skillGroups, "");
		if (empty($skillGroups)) {
			$this->setSkillGroups(json_decode('[{"id":0,"name":"Level 1"}]'));
			return json_decode('[{"id":0,"name":"Level 1"}]');
		}
		$groups = json_decode($skillGroups, true);
		if (!is_array($groups)) {
			$groups = array();
		}

		if (empty($groups)) {
			$this->setSkillGroups(json_decode('[{"id":0,"name":"Level 1"}]'));
			$groups = json_decode('[{"id":0,"name":"Level 1"}]');
		}
		return $groups;
	}
}
