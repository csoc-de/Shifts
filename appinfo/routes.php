<?php

return [
	'resources' => [
		'shift' => ['url' => '/shifts'],
		'shiftsType' => ['url' => '/shiftsType']
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'shift#getGroupStatus', 'url' => '/checkAdmin', 'verb' => 'GET'],
		['name' => 'shift#getAllAnalysts', 'url' => '/getAllAnalysts', 'verb' => 'GET'],
	]
];
