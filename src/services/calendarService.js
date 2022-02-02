/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

import getTimezoneManager from './timezoneDataProviderService'
import jstz from 'jstz'
import { DateTimeValue, AttendeeProperty, createEvent, getParserManager } from '@nextcloud/calendar-js'
import { findAllCalendars } from './caldavService'
import { calcShiftDate } from '../utils/date'
import store from '../store'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

/**
 * returns the calDav conform Timezone
 *
 * @return {Timezone}
 */
const findCurrentTimezone = () => {
	const timezoneManager = getTimezoneManager()
	const determinedTimezone = jstz.determine()
	return timezoneManager.getTimezoneForId(determinedTimezone.name())
}

const timezone = findCurrentTimezone()

const syncAllShiftsChanges = async (shiftsList, shiftTypes, allAnalysts) => {
	const shiftsCalendar = await findShiftsCalendar()
	const groups = shiftsList.reduce((array, item) => {
		const group = (array[item.shiftDate] || [])
		group.push(item)
		array[item.shiftDate] = group
		return array
	}, {})
	for (const group in groups) {
		const changes = groups[group]
		let currShiftTypeId = '-1'
		let vObject
		let eventComponent
		for (const change of changes) {
			try {
				if (change.shiftTypeId !== currShiftTypeId) {
					try {
						[vObject, eventComponent] = await findEventComponent(shiftsCalendar, group, change.shiftsType, timezone)
					} catch (e) {
						if (e.message.includes('Could not find corresponding Event')) {
							const timestamps = getTimestamps(group, change.shiftsType)
							eventComponent = createEventComponent(
								timestamps[0],
								timestamps[1],
								change.shiftsType.isWeekly)
							eventComponent.setOrganizerFromNameAndEMail(getOrganizerName(), getOrganizerEmail())

							eventComponent.title = change.shiftsType.name + ': '
							if (eventComponent.isDirty()) {
								vObject = await calendar.createVObject(eventComponent.root.toICS())
							}
						} else {
							throw new Error(e)
						}
					}
					currShiftTypeId = change.shiftsType.id
				}
				if (change.action === 'update') {
					const removeAnalyst = allAnalysts.find((analyst) => analyst.uid === change.oldUserId)
					const newAnalyst = allAnalysts.find((analyst) => analyst.uid === change.newUserId)

					eventComponent = await addAnalystToEvent(eventComponent, newAnalyst)
					if (removeAnalyst !== undefined) {
						eventComponent = await removeAnalystFromEvent(eventComponent, removeAnalyst)
					}
				}
				if (change.action === 'unassign') {
					const removeAnalyst = allAnalysts.find((analyst) => analyst.uid === change.oldUserId)

					eventComponent = await removeAnalystFromEvent(eventComponent, removeAnalyst)
				}
				const attendeeIteratorCheck = eventComponent.getPropertyIterator('ATTENDEE')
				const attendeesCheck = Array.from(attendeeIteratorCheck)
				if (attendeesCheck.length > 0) {
					if (eventComponent.isDirty()) {
						vObject.data = eventComponent.root.toICS()
						await vObject.update()
					}
				} else if (attendeesCheck.length === 0) {
					await vObject.delete()
				}
				change.isDone = true
				await axios.put(generateUrl(`/apps/shifts/shiftsCalendarChange/${change.id}`), change)
			} catch (e) {
				throw new Error(e)
			}
		}
	}
}

const syncAll = async (shiftsList, shiftTypes, allAnalysts) => {
	const shiftsCalendar = await findShiftsCalendar()
	const groups = shiftsList.reduce((array, item) => {
		const group = (array[item.date] || [])
		group.push(item)
		array[item.date] = group
		return array
	}, {})
	for (const group in groups) {
		for (const shiftType of shiftTypes) {
			const analysts = []
			const shifts = groups[group].filter(item => item.shiftTypeId === shiftType.id.toString())
			shifts.forEach(shift => {
				const foundAnalyst = allAnalysts.find((analyst) => {
					return shift.userId === analyst.uid
				})
				if (foundAnalyst) {
					analysts.push(foundAnalyst)
				}
			})
			if (analysts.length > 0) {
				await syncCalendarObject(shiftsCalendar, shiftType, group, analysts)
			} else {
				try {
					// eslint-disable-next-line
					const [vObject, eventComponent] = await findEventComponent(calendar, group, shiftType, timezone)
					const date = new Date(group)
					if (!(shiftType.isWeekly === '1' && date.getDay() !== 1)) {
						await vObject.delete()
					}
				} catch (e) {
					// do nothing when no Event is found
				}
			}
		}
	}
}

const updateExistingCalendarObjectFromShiftsChange = async (oldShift, newShift, oldAnalyst, newAnalyst) => {
	const oldShiftsDate = oldShift.date
	const oldShiftsType = oldShift.shiftsType

	const timezone = findCurrentTimezone()
	const shiftsCalendar = await findShiftsCalendar(getCalendarName())

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

/**
 * synchronizes the calendar for a given ShiftsType and a list of
 * @param {Calendar} calendar Shifts-Calendar
 * @param {object} shiftsType ShiftsType of the Shift
 * @param {string} dateString Date of Shifts
 * @param {Array} analysts list of participating analysts
 */
// eslint-disable-next-line
const syncCalendarObject = async(calendar, shiftsType, dateString, analysts) => {
	try {
		// eslint-disable-next-line
		const [vObject, eventComponent] = await findEventComponent(calendar, dateString, shiftsType, timezone)
		const attendeeIterator = eventComponent.getPropertyIterator('ATTENDEE')
		const attendees = Array.from(attendeeIterator)
		for (const analyst of analysts) {
			if (!attendees.some(attendee => attendee.email === 'mailto:' + analyst.email)) {
				const attendee = createAttendeeFromAnalyst(analyst)

				eventComponent.addProperty(attendee)

				eventComponent.title = eventComponent.title + ' ' + analyst.name
			}
		}
		for (const attendee of attendees) {
			if (!analysts.some(analyst => attendee.email === 'mailto:' + analyst.email)) {
				eventComponent.removeAttendee(attendee)

				eventComponent.title = eventComponent.title.replace(attendee.commonName, '')
			}
		}
		const attendeeIteratorCheck = eventComponent.getPropertyIterator('ATTENDEE')
		const attendeesCheck = Array.from(attendeeIteratorCheck)
		if (attendeesCheck.length > 0) {
			if (eventComponent.isDirty()) {
				vObject.data = eventComponent.root.toICS()
				await vObject.update()
			}
		} else if (attendeesCheck.length === 0) {
			await vObject.delete()
		}
	} catch (e) {
		if (e.message.includes('Could not find corresponding Event')) {
			const timestamps = getTimestamps(dateString, shiftsType)
			const eventComponent = createEventComponent(
				timestamps[0],
				timestamps[1],
				shiftsType.isWeekly)
			let title = shiftsType.name + ': '

			eventComponent.setOrganizerFromNameAndEMail(getOrganizerName(), getOrganizerEmail())
			analysts.forEach((analyst) => {
				const attendee = createAttendeeFromAnalyst(analyst)
				title = title + ' ' + analyst.name
				eventComponent.addProperty(attendee)
			})

			eventComponent.title = title
			if (eventComponent.isDirty()) {
				await calendar.createVObject(eventComponent.root.toICS())
			}
		} else {
			throw new Error(e)
		}
	}
}

/**
 * adds analyst to EventComponent
 * @param {any} eventComponent The event to be edited
 * @param {object} analyst The given analyst
 * @return {any}
 */
const addAnalystToEvent = async (eventComponent, analyst) => {
	const attendeeIterator = eventComponent.getPropertyIterator('ATTENDEE')
	const attendees = Array.from(attendeeIterator)
	if (!attendees.some(attendee => attendee.email === 'mailto:' + analyst.email)) {
		const attendee = createAttendeeFromAnalyst(analyst)

		eventComponent.addProperty(attendee)

		eventComponent.title = eventComponent.title + ' ' + analyst.name
	}
	return eventComponent
}

/**
 * removes analyst from EventComponent
 * @param {any} eventComponent The event to be edited
 * @param {object} analyst The given analyst
 * @return {any}
 */
const removeAnalystFromEvent = async (eventComponent, analyst) => {
	const attendeeIterator = eventComponent.getPropertyIterator('ATTENDEE')
	const attendees = Array.from(attendeeIterator)
	for (const attendee of attendees) {
		if (attendee.email === 'mailto:' + analyst.email) {
			eventComponent.removeAttendee(attendee)

			eventComponent.title = eventComponent.title.replace(attendee.commonName, '')
		}
	}
	return eventComponent
}

/**
 * returns the calendarName
 *
 * @return {string}
 */
let calendarName
const getCalendarName = () => {
	if (!calendarName) {
		calendarName = store.getters.getCalendarName
	}
	return calendarName
}

/**
 * returns the organizerName
 *
 * @return {string}
 */
let organizerName
const getOrganizerName = () => {
	if (!organizerName) {
		organizerName = store.getters.getOrganizerName
	}
	return organizerName
}

/**
 * returns the organizerEmail
 *
 * @return {string}
 */
let organizerEmail
const getOrganizerEmail = () => {
	if (!organizerEmail) {
		organizerEmail = store.getters.getOrganizerEmail
	}
	return organizerEmail
}

/**
 * returns the dedicated Shifts-Calendar based on the Organizers name
 *
 * @return {Calendar}
 */
let calendar
const findShiftsCalendar = async () => {
	if (!calendar || calendar.owner.includes(getOrganizerName())) {
		const calendars = await findAllCalendars()
		calendar = calendars.find(calendar => {
			return calendar.owner.includes(getOrganizerName()) && calendar.displayname.includes(getCalendarName())
		})
		return calendar
	} else {
		return calendar
	}
}

/**
 * creates an Eventcomponent from Timestamps
 *
 * @param {Date} startDate Date of start of new event
 * @param {Date} stopDate Date of stop of new event
 * @param {boolean} isWeekly Determines if Event is is all Day and Weekly*
 * @return {EventComponent}
 */
const createEventComponent = (startDate, stopDate, isWeekly) => {
	const startDateTime = DateTimeValue
		.fromJSDate(startDate, true)
		.getInTimezone(timezone)
	const endDateTime = DateTimeValue
		.fromJSDate(stopDate, true)
		.getInTimezone(timezone)

	if (isWeekly === '1') {
		startDateTime.isDate = true
		endDateTime.isDate = true
	}

	const calendarComponent = createEvent(startDateTime, endDateTime)
	for (const vObject of calendarComponent.getVObjectIterator()) {
		vObject.undirtify()
	}

	const iterator = calendarComponent.getVObjectIterator()

	const firstVObject = iterator.next().value
	if (!firstVObject) {
		throw new Error('Could not find Event')
	}

	return firstVObject
}

/**
 * returns AttendeeProperty from Analyst
 *
 * @param {object} analyst Analyst-Object of attendee to be created
 * @return {AttendeeProperty}
 */
const createAttendeeFromAnalyst = (analyst) => {
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
 * @param {object} removeAnalyst Old Analyst Object to be removed from EventComponent
 * @param {object} addAnalyst New Analyst Object to be added to EventComponent
 * @param {Timezone} timezone Timezone of the current instance
 * @return {EventComponent}
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

	const newAttendee = createAttendeeFromAnalyst(addAnalyst)

	event.addProperty(newAttendee)

	return event
}

/**
 * find EventComponent by Date
 *
 * @param {Calendar} calendar Shifts-Calendar to find Events in
 * @param {string} dateString Date-String of the Shift
 * @param {object} shiftsType ShiftsType of the Shift
 * @param {Timezone} timezone Timezone of the current instance
 * @return {[VObject,EventComponent]}
 */
const findEventComponent = async (calendar, dateString, shiftsType, timezone) => {
	const timestamps = getTimestamps(dateString, shiftsType)
	console.log(timestamps)
	const vObjects = await calendar.findByTypeInTimeRange('VEVENT', timestamps[0], timestamps[1])

	if (vObjects.length <= 0) {
		throw new Error('Could not find corresponding Events')
	}
	const cleanDateString = dateString.replaceAll('-', '')
	let vObject
	for (const obj of vObjects) {
		if (obj.data.includes(`SUMMARY:${shiftsType.name}:`) && (obj.data.includes(`DTSTART;VALUE=DATE:${cleanDateString}`) || shiftsType.isWeekly === '0')) {
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

	return [vObject, firstVObject]
}

/**
 * returns the corresponding timestamps
 *
 * @param {string} dateString start Date of Shift
 * @param {object} shiftsType type of shift
 * @return {[Date, Date]}
 */
const getTimestamps = (dateString, shiftsType) => {
	if (shiftsType.isWeekly === '1') {
		const start = calcShiftDate(dateString, '00:00')
		const end = calcShiftDate(dateString, '00:00')
		end.setDate(end.getDate() + 7)
		return [start, end]
	} else {
		return [
			calcShiftDate(dateString, shiftsType.startTimestamp),
			calcShiftDate(dateString, shiftsType.stopTimestamp),
		]
	}
}

export {
	updateExistingCalendarObjectFromShiftsChange,
	syncAllShiftsChanges,
	syncAll,
}
