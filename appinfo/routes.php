<?php

return [
	'resources' => [
		'shift' => ['url' => '/shifts'],
		'shiftsType' => ['url' => '/shiftsType'],
		'shiftsChange' => ['url' => '/shiftsChange']
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#index', 'url' => '/timeline', 'verb' => 'GET', 'postfix' => 'direct.timeline'],
		['name' => 'shift#getGroupStatus', 'url' => '/checkAdmin', 'verb' => 'GET'],
		['name' => 'shift#getAllAnalysts', 'url' => '/getAllAnalysts', 'verb' => 'GET'],
		['name' => 'shift#getAnalystsExcludingCurrent', 'url' => '/getAnalysts', 'verb' => 'GET'],
		['name' => 'shift#getShiftsByUserId', 'url' => '/shifts/getAllByUserId', 'verb' => 'GET'],
		['name' => 'shift#getCurrentUserId', 'url' => '/getCurrentUserId', 'verb' => 'GET'],
		['name' => 'shift#triggerUnassignedShifts', 'url' => '/triggerUnassignedShifts', 'verb' => 'GET'],
		['name' => 'shift#getAssignedShifts', 'url' => '/getAssignedShifts', 'verb' => 'GET'],
		['name' => 'shift#getShiftsDataByTimeRange', 'url' => '/getShiftsDataByTimeRange/{start}/{end}', 'verb' => 'GET'],
		['name' => 'settings#getSettings', 'url' => '/settings', 'verb' => 'GET'],
		['name' => 'settings#saveSettings', 'url' => '/settings', 'verb' => 'PUT'],
	]
];
