<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

namespace OCA\Shifts\AppInfo;

use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;
use OCP\AppFramework\Http\ContentSecurityPolicy;

class Application extends App{
	public const APP_ID = 'shifts';

	public function __construct() {
		parent::__construct(self::APP_ID);

		$container = $this->getContainer();

		$container->registerService('URLGenerator', function($c) {
			return $c->query('ServerContainer')->getURLGenerator();
		});
	}
}
