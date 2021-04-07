<?php


namespace OCA\Shifts\Settings;

use OCP\IURLGenerator;
use OCP\Settings\IIconSection;


class AdminSection implements  IIconSection {

	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct(IURLGenerator $urlGenerator) {
		$this->urlGenerator = $urlGenerator;
	}
	/**
	 * ID
	 *
	 * @return string
	 */
	public function getID(): string {
		return 'shifts';
	}

	/**
	 * Icon
	 *
	 * @return string
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath("shifts", "app.svg");
	}

	/**
	 * Get Name
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'Shifts';
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
