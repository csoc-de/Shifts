/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */
import tzData from '../../timezones/zones.json'
import { getTimezoneManager } from 'calendar-js'
import logger from '../utils/logger.js'

const timezoneManager = getTimezoneManager()
let initialized = false

/**
 * Gets the timezone-manager
 * initializes it if necessary
 *
 * @returns {TimezoneManager}
 */
export default function() {
	if (!initialized) {
		initialize()
	}

	return timezoneManager
}

/**
 * Initializes the timezone-manager with all timezones shipped by the calendar app
 */
function initialize() {
	logger.debug(`The calendar app is using version ${tzData.version} of the timezone database`)

	for (const tzid in tzData.zones) {
		if (Object.prototype.hasOwnProperty.call(tzData.zones, [tzid])) {
			const ics = [
				'BEGIN:VTIMEZONE',
				'TZID:' + tzid,
				...tzData.zones[tzid].ics,
				'END:VTIMEZONE',
			].join('\r\n')
			timezoneManager.registerTimezoneFromICS(tzid, ics)
		}
	}

	for (const tzid in tzData.aliases) {
		if (Object.prototype.hasOwnProperty.call(tzData.aliases, [tzid])) {
			timezoneManager.registerAlias(tzid, tzData.aliases[tzid].aliasTo)
		}
	}

	initialized = true
}
