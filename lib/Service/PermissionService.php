<?php
/*
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

namespace OCA\Shifts\Service;

use OCP\IGroupManager;
use OCA\Shifts\Settings\Settings;

class PermissionService {

	/** @var IGroupManager */
	private $groupManager;

	/** @var Settings */
	private $settings;

	/** @var UserID */
    private $userId;

	public function __construct(IGroupManager $groupManager, Settings $settings, $userId){
		$this->groupManager = $groupManager;
		$this->settings = $settings;
		$this->userId = $userId;
	}

	public function getUserId(): string {
		return $this->userId;
	}

	public function isAdmin(string $userId): bool {
		return $this->groupManager->isInGroup($userId, $this->settings->getAdminGroup());
	}

	public function isRequestingUserAdmin(): bool {
		return $this->groupManager->isInGroup($this->userId, $this->settings->getAdminGroup());
	}

	public function isRequestingUser(string $userId): bool {
		return $this->userId == $userId;
	}
}
