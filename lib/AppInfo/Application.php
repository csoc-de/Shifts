<?php

namespace OCA\Shifts\AppInfo;

use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;
use OCP\AppFramework\Http\ContentSecurityPolicy;

class Application extends App {
	public const APP_ID = 'shifts';

	public function __construct() {
		parent::__construct(self::APP_ID);

		$container = $this->getContainer();

		$dispatcher = $container->query(IEventDispatcher::class);
		$dispatcher->addListener(AddContentSecurityPolicyEvent::class, function (AddContentSecurityPolicyEvent $event){
			$event->addPolicy($this->createCsp());
		});
	}

	private function createCsp(): ContentSecurityPolicy {
		$csp = new ContentSecurityPolicy();
		$csp->addAllowedConnectDomain('https://gstc.neuronet.io/');
		return $csp;
	}
}
