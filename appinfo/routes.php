<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

return [
	'resources' => [
		'shift' => ['url' => '/shifts'],
		'shiftsType' => ['url' => '/shiftsType'],
		'shiftsChange' => ['url' => '/shiftsChange'],
		'shiftsCalendar' => ['url' => '/shiftsCalendar'],
		'shiftsCalendarChange' => ['url' => '/shiftsCalendarChange'],
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#index', 'url' => '/timeline', 'verb' => 'GET', 'postfix' => 'direct.timeline'],
		['name' => 'page#index', 'url' => '/requests', 'verb' => 'GET', 'postfix' => 'direct.requests'],
		['name' => 'page#index', 'url' => '/shiftsTypes', 'verb' => 'GET', 'postfix' => 'direct.shiftsTypes'],
		['name' => 'page#index', 'url' => '/archive', 'verb' => 'GET', 'postfix' => 'direct.archive'],
		['name' => 'shift#getGroupStatus', 'url' => '/checkAdmin', 'verb' => 'GET'],
		['name' => 'shift#getAllAnalysts', 'url' => '/getAllAnalysts', 'verb' => 'GET'],
		['name' => 'shift#getAnalystsExcludingCurrent', 'url' => '/getAnalysts', 'verb' => 'GET'],
		['name' => 'shift#getShiftsByUserId', 'url' => '/shifts/getAllByUserId', 'verb' => 'GET'],
		['name' => 'shift#getCurrentUserId', 'url' => '/getCurrentUserId', 'verb' => 'GET'],
		['name' => 'shift#triggerUnassignedShifts', 'url' => '/triggerUnassignedShifts', 'verb' => 'GET'],
		['name' => 'shift#getShiftsDataByTimeRange', 'url' => '/getShiftsDataByTimeRange/{start}/{end}', 'verb' => 'GET'],
		['name' => 'settings#getSettings', 'url' => '/settings', 'verb' => 'GET'],
		['name' => 'settings#saveSettings', 'url' => '/settings', 'verb' => 'PUT'],
		['name' => 'shiftsCalendar#index', 'url' => '/shiftsCalendar', 'verb' => 'GET'],
		['name' => 'shiftsCalendar#create', 'url' => '/shiftsCalendar', 'verb' => 'POST'],
		['name' => 'shiftsCalendar#update', 'url' => '/shiftsCalendar', 'verb' => 'PUT'],
		['name' => 'shiftsCalendar#delete', 'url' => '/shiftsCalendar', 'verb' => 'DELETE'],
		['name' => 'shiftsCalendar#synchronize', 'url' => '/shiftsCalendar', 'verb' => 'PATCH'],
	]
];
