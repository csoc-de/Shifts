/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@outlook.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@outlook.de>
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'

const state = {
	calendarName: '',
	organizerName: '',
	organizerEmail: '',
	skillGroups: '',
	gstcLicense: '',
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
	updateGstcLicense(state, license) {
		state.gstcLicense = license
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
	getGstcLicense(state) {
		return state.gstcLicense
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
			commit('updateGstcLicense', settings.gstcLicense)
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
		}
	},
}

export default { state, mutations, getters, actions }
