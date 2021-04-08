import getTimezoneManager from './timezoneDataProviderService'
import jstz from 'jstz'
import DateTimeValue from 'calendar-js/src/values/dateTimeValue'
import { createEvent, getParserManager } from 'calendar-js'
import { findAllCalendars } from './caldavService'
import { calcShiftDate } from '../utils/date'
import AttendeeProperty from 'calendar-js/src/properties/attendeeProperty'

const organizerName = 'admin'
const organizerEmail = 'technik@csoc.de'

const saveCalendarObjectFromNewShift = async(newShift) => {
	const dates = newShift.dates
	const shiftsType = newShift.shiftsType
	const analysts = newShift.analysts

	const timezone = findCurrentTimezone()
	const shiftsCalendar = await findShiftsCalendar()
	await Promise.all(dates.map(async(date) => {
		const eventComponent = createEventComponent(
			calcShiftDate(date, shiftsType.startTimeStamp),
			calcShiftDate(date, shiftsType.stopTimeStamp),
			timezone)

		let title = shiftsType.name + ': '

		eventComponent.setOrganizerFromNameAndEMail(organizerName, organizerEmail)

		analysts.forEach((analyst) => {
			const attendee = createAttendeeFromAnalyst(analyst, timezone)
			title = title + ' ' + analyst.commonName
			eventComponent.addProperty(attendee)
		})

		eventComponent.title = title

		if (eventComponent.isDirty()) {
			await shiftsCalendar.createVObject(eventComponent.root.toICS())
		}
	}))
}

const updateExistingCalendarObjectFromShiftsChange = async(oldShift, newShift, oldAnalyst, newAnalyst) => {
	const oldShiftsDate = oldShift.date
	const oldShiftsType = oldShift.shiftsType

	const timezone = findCurrentTimezone()
	const shiftsCalendar = await findShiftsCalendar('Leitstellen Schichtplan')

	let [oldVObject, oldEventComponent] = await findEventComponent(shiftsCalendar, oldShiftsDate, oldShiftsType, timezone)

	oldEventComponent = editEventComponent(oldEventComponent, oldAnalyst, newAnalyst, timezone)

	if (oldEventComponent.isDirty()) {
		oldVObject.data = oldEventComponent.root.toICS()
		await oldVObject.update()
	}

	if (newShift) {
		const newShiftsType = newShift.shiftsType
		const newShiftsDate = newShift.date
		let [newVObject, newEventComponent] = await findEventComponent(shiftsCalendar, newShiftsDate, newShiftsType, timezone)

		newEventComponent = editEventComponent(newEventComponent, newAnalyst, oldAnalyst, timezone)

		if (newEventComponent.isDirty()) {
			newVObject.data = newEventComponent.root.toICS()
			await newVObject.update()
		}
	}
}

const moveExistingCalendarObject = async(shiftsType, oldDate, newDate) => {
	const timezone = findCurrentTimezone()
	const shiftsCalendar = await findShiftsCalendar('Leitstellen Schichtplan')

	const [vObject, eventComponent] = await findEventComponent(shiftsCalendar, oldDate, shiftsType, timezone)

	const startDate = calcShiftDate(newDate, shiftsType.startTimeStamp)
	eventComponent.startDate.year = startDate.getFullYear()
	eventComponent.startDate.month = startDate.getMonth() + 1
	eventComponent.startDate.day = startDate.getDate()

	const endDate = calcShiftDate(newDate, shiftsType.stopTimeStamp)
	eventComponent.endDate.year = endDate.getFullYear()
	eventComponent.endDate.month = endDate.getMonth() + 1
	eventComponent.endDate.day = endDate.getDate()

	if (eventComponent.isDirty()) {
		vObject.data = eventComponent.root.toICS()
		console.log(vObject.data)
		await vObject.update()
	}
}

/**
 * returns the dedicated Shifts-Calendar based on the Organizers name
 *
 * @returns {Calendar}
 */
let calendar
const findShiftsCalendar = async() => {
	if (!calendar || calendar.owner.includes(organizerName)) {
		const calendars = await findAllCalendars()
		calendar = calendars.find(calendar => {
			return calendar.owner.includes(organizerName) && calendar.displayname.includes('Leitstellen')
		})
		return calendar
	} else {
		return calendar
	}
}

/**
 * returns the calDav conform Timezone
 *
 * @returns {Timezone}
 */
const findCurrentTimezone = () => {
	const timezoneManager = getTimezoneManager()
	const determinedTimezone = jstz.determine()
	return timezoneManager.getTimezoneForId(determinedTimezone.name())
}

/**
 * creates an Eventcomponent from Timestamps
 *
 * @param {Date} startDate Date of start of new event
 * @param {Date} stopDate Date of stop of new event
 * @param {Timezone} timezone Timezone of new event
 *
 * @returns {EventComponent}
 */
const createEventComponent = (startDate, stopDate, timezone) => {
	const startDateTime = DateTimeValue
		.fromJSDate(startDate, true)
		.getInTimezone(timezone)
	const endDateTime = DateTimeValue
		.fromJSDate(stopDate, true)
		.getInTimezone(timezone)

	const calendarComponent = createEvent(startDateTime, endDateTime)
	for (const vObject of calendarComponent.getVObjectIterator()) {
		vObject.undirtify()
	}

	const iterator = calendarComponent.getVObjectIterator()

	const firstVObject = iterator.next().value
	if (!firstVObject) {
		throw new Error('Could not find Event')
	}

	return firstVObject.recurrenceManager.getOccurrenceAtExactly(startDateTime)
}

/**
 * returns AttendeeProperty from Analyst
 *
 * @param {Object} analyst Analyst-Object of attendee to be created
 * @param {Timezone} timezone Timezone of the current instance
 *
 * @returns {AttendeeProperty}
 */
const createAttendeeFromAnalyst = (analyst, timezone) => {
	let name = ''
	if (analyst.name) {
		name = analyst.name
	} else {
		name = analyst.commonName
	}
	const attendee = AttendeeProperty.fromNameAndEMail(name, analyst.email)

	attendee.userType = 'INDIVIDUAL'
	attendee.participationStatus = 'NEEDS-ACTION'
	attendee.role = 'REQ-PARTICIPANT'
	attendee.rsvp = true
	attendee.updateParameterIfExist('TZID', timezone.timezoneId)

	return attendee
}

/**
 * edit EventComponent
 *
 * @param {EventComponent} event Event to be edited
 * @param {Object} removeAnalyst Old Analyst Object to be removed from EventComponent
 * @param {Object} addAnalyst New Analyst Object to be added to EventComponent
 * @param {Timezone} timezone Timezone of the current instance
 *
 * @returns {EventComponent}
 */

const editEventComponent = (event, removeAnalyst, addAnalyst, timezone) => {
	const attendeeIterator = event.getPropertyIterator('ATTENDEE')

	let attendeeToBeRemoved
	for (const attendee of attendeeIterator) {
		if (attendee.email === 'mailto:' + removeAnalyst.email) {
			attendeeToBeRemoved = attendee
			break
		}
	}

	if (!attendeeToBeRemoved) {
		throw new Error('Could not edit Event')
	}
	event.removeAttendee(attendeeToBeRemoved)

	event.title = event.title.replace(removeAnalyst.name, addAnalyst.name)

	const newAttendee = createAttendeeFromAnalyst(addAnalyst, timezone)

	event.addProperty(newAttendee)

	return event
}

/**
 * find EventComponent by Date
 *
 * @param {Calendar} calendar Shifts-Calendar to find Events in
 * @param {String} dateString Date-String of the Shift
 * @param {Object} shiftsType ShiftsType of the Shift
 * @param {Timezone} timezone Timezone of the current instance
 *
 * @returns {[VObject,EventComponent]}
 */
const findEventComponent = async(calendar, dateString, shiftsType, timezone) => {
	const vObjects = await calendar.findByTypeInTimeRange('VEVENT',
		calcShiftDate(dateString, shiftsType.startTimeStamp),
		calcShiftDate(dateString, shiftsType.stopTimeStamp))
	if (vObjects.length <= 0) {
		throw new Error('Could not find corresponding Events')
	}
	let vObject
	for (const obj of vObjects) {
		if (obj.data.includes(`SUMMARY:${shiftsType.name}`)) {
			vObject = obj
			break
		}
	}
	if (!vObject) {
		throw new Error('Could not find corresponding Event')
	}

	const parserManager = getParserManager()
	const parser = parserManager.getParserForFileType('text/calendar')

	// This should not be the case, but let's just be on the safe side
	if (typeof vObject.data !== 'string' || vObject.data.trim() === '') {
		throw new Error('Empty calendar object')
	}

	parser.parse(vObject.data)

	const calendarComponentIterator = parser.getItemIterator()
	const calendarComponent = calendarComponentIterator.next().value
	if (!calendarComponent) {
		throw new Error('Empty calendar object')
	}
	const iterator = calendarComponent.getVObjectIterator()
	const firstVObject = iterator.next().value
	if (!firstVObject) {
		return
	}

	const startDateTime = DateTimeValue
		.fromJSDate(calcShiftDate(dateString, shiftsType.startTimeStamp), true)
		.getInTimezone(timezone)

	return [vObject, firstVObject.recurrenceManager.getOccurrenceAtExactly(startDateTime)]
}

export {
	saveCalendarObjectFromNewShift,
	updateExistingCalendarObjectFromShiftsChange,
	moveExistingCalendarObject,
}
