/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError, showWarning } from '@nextcloud/dialogs'

const state = {
	calendarName: null,
	addUserCalendarEvent: true,
	organizerName: null,
	organizerEmail: null,
	timezone: 'UTC',
	skillGroups: null,
	shiftChangeSameShiftType: false,
	settingsFetched: false,
}

const getters = {
	getCalendarName(state) {
		return state.calendarName
	},
	getAddUserCalendarEvent(state) {
		return state.addUserCalendarEvent
	},
	getOrganizerName(state) {
		return state.organizerName
	},
	getOrganizerEmail(state) {
		return state.organizerEmail
	},
	getShiftsTimezone(state) {
		return state.timezone
	},
	getSkillGroups(state) {
		return state.skillGroups
	},
	getShiftChangeSameType(state) {
		return state.shiftChangeSameShiftType
	},
	getSettingsFetched(state) {
		return state.settingsFetched
	},
}

const actions = {
	async fetchSettings({ state, dispatch, commit }) {
		try {
			const settingsResponse = await axios.get(generateUrl('/apps/shifts/settings'))
			const settings = settingsResponse.data

			commit('updateCalendarName', settings.calendarName)
			commit('updateAddUserCalendarEvent', settings.addUserCalendarEvent)
			commit('updateOrganizerName', settings.organizerName)
			commit('updateOrganizerEmail', settings.organizerEmail)
			commit('updateShiftsTimezone', settings.timezone)
			commit('updateSkillGroups', settings.skillGroups)
			commit('updateShiftChangeSameType', settings.shiftChangeSameShiftType === '1')
			commit('updateSettingsFetched', true)

		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch Settings'))
		}
		if (state.calendarName === '') {
			showWarning(t('shifts', 'Please set a Calendarname in the App-Settings'))
			commit('updateSettingsFetched', false)
		}
		if (state.organizerName === '') {
			showWarning(t('shifts', 'Please set an Organizername in the App-Settings'))
			commit('updateSettingsFetched', false)
		}
		if (state.organizerEmail === '') {
			showWarning(t('shifts', 'Please set an Organizeremail in the App-Settings'))
			commit('updateSettingsFetched', false)
		}
		if (state.skillGroups !== null && state.skillGroups.length === 0) {
			showWarning(t('shifts', 'Please set at least one Skillgroup in the App-Settings'))
			commit('updateSettingsFetched', false)
		}
	},
}

const mutations = {
	updateCalendarName(state, calendarName) {
		state.calendarName = calendarName
	},
	updateAddUserCalendarEvent(state, addEvent) {
		state.addUserCalendarEvent = addEvent
	},
	updateOrganizerName(state, organizerName) {
		state.organizerName = organizerName
	},
	updateOrganizerEmail(state, organizerEmail) {
		state.organizerEmail = organizerEmail
	},
	updateShiftChangeSameType(store, shiftChangeSameType) {
		state.shiftChangeSameShiftType = shiftChangeSameType
	},
	updateSkillGroups(state, skillGroups) {
		state.skillGroups = skillGroups
	},
	updateShiftsTimezone(state, timezone) {
		state.timezone = timezone
	},
	updateSettingsFetched(state, fetched) {
		state.settingsFetched = fetched
	},
}

export default { state, mutations, getters, actions }
