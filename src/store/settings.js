/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError, showWarning } from '@nextcloud/dialogs'

const state = {
	calendarName: null,
	organizerName: null,
	organizerEmail: null,
	skillGroups: null,
	settingsFetched: false,
}

const mutations = {
	updateCalendarName(state, calendarName) {
		state.calendarName = calendarName
	},
	updateOrganizerName(state, organizerName) {
		state.organizerName = organizerName
	},
	updateOrganizerEmail(state, organizerEmail) {
		state.organizerEmail = organizerEmail
	},
	updateSkillGroups(state, skillGroups) {
		state.skillGroups = skillGroups
	},
	updateSettingsFetched(state, fetched) {
		state.settingsFetched = fetched
	},
}

const getters = {
	getCalendarName(state) {
		return state.calendarName
	},
	getOrganizerName(state) {
		return state.organizerName
	},
	getOrganizerEmail(state) {
		return state.organizerEmail
	},
	getSkillGroups(state) {
		return state.skillGroups
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
			commit('updateOrganizerName', settings.organizerName)
			commit('updateOrganizerEmail', settings.organizerEmail)
			commit('updateSkillGroups', settings.skillGroups)
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

export default { state, mutations, getters, actions }
