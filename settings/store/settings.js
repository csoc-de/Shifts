/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Vue from 'vue'

const state = {
	calendarName: '',
	addUserCalendarEvent: true,
	analystGroup: '',
	shiftAdminGroup: '',

	organizerName: '',
	organizerEmail: '',
	timezone: '',

	skillGroups: [],

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
	getAnalystGroup(state) {
		return state.analystGroup
	},
	getShiftAdminGroup(state) {
		return state.shiftAdminGroup
	},

	getOrganizerName(state) {
		return state.organizerName
	},
	getOrganizerEmail(state) {
		return state.organizerEmail
	},
	getShiftTimezone(state) {
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
	fetchSettings(store) {
		return new Promise((resolve, reject) => {
			axios.get(generateUrl('/apps/shifts/settings')).then((result) => {
				if (result.status === 200 && result.data) {
					const settings = result.data
					console.debug('SETTINGS:', settings)

					store.commit('updateCalendarName', settings.calendarName)
					store.commit('updateAddUserCalendarEvent', settings.addUserCalendarEvent)
					store.commit('updateAnalystGroup', settings.shiftWorkerGroup)
					store.commit('updateShiftAdminGroup', settings.adminGroup)
					store.commit('updateOrganizerName', settings.organizerName)
					store.commit('updateOrganizerEmail', settings.organizerEmail)
					store.commit('updateShiftTimezone', settings.timezone)
					store.commit('updateSkillGroups', settings.skillGroups)

					if (typeof settings.shiftChangeSameShiftType === 'string') {
						settings.shiftChangeSameShiftType = settings.shiftChangeSameShiftType === '1'
					}
					store.commit('updateShiftChangeSameType', settings.shiftChangeSameShiftType)

					store.commit('updateSettingsFetched', true)

					resolve()
				} else {
					reject(new Error('Failed to load settings: ' + result.statusText))
				}
			}).catch((e) => {
				reject(e)
			})
		})
	},

	saveSettings(store) {
		return new Promise((resolve, reject) => {
			console.debug('SAMESHIFT:', store.state.shiftChangeSameShiftType)
			axios.put(generateUrl('/apps/shifts/settings'), {
				calendarName: store.state.calendarName,
				addUserCalendarEvent: store.state.addUserCalendarEvent,
				shiftWorkerGroup: store.state.analystGroup,
				adminGroup: store.state.shiftAdminGroup,
				organizerName: store.state.organizerName,
				organizerEmail: store.state.organizerEmail,
				timezone: store.state.timezone,
				skillGroups: store.state.skillGroups,
				shiftChangeSameShiftType: store.state.shiftChangeSameShiftType ? '1' : '0',
			}).then(() => {
				resolve()
			}).catch((e) => {
				console.error('Could not update settings:', e)
				reject(e)
			})
		})
	},

	updateCalendarName(store, calendarName) {
		store.commit('updateCalendarName', calendarName)
	},
	updateAddUserCalendarEvent(store, addEvent) {
		store.commit('updateAddUserCalendarEvent', addEvent)
	},
	updateAnalystGroup(store, analystGroup) {
		store.commit('updateAnalystGroup', analystGroup)
	},
	updateShiftAdminGroup(store, shiftAdminGroup) {
		store.commit('updateShiftAdminGroup', shiftAdminGroup)
	},
	updateShiftTimezone(store, timezone) {
		store.commit('updateShiftTimezone', timezone)
	},

	updateOrganizerName(store, organizerName) {
		store.commit('updateOrganizerName', organizerName)
	},
	updateOrganizerEmail(store, organizerEmail) {
		store.commit('updateOrganizerEmail', organizerEmail)
	},

	updateShiftChangeSameType(store, shiftChangeSameType) {
		store.commit('updateShiftChangeSameType', shiftChangeSameType)
	},

	addEmptyRowToSkillGroups(store) {
		const len = store.state.skillGroups.length
		store.commit('addEmptyRowToSkillGroups', parseInt(store.state.skillGroups[len - 1].id) + 1)
	},
	removeSkillGroup(store, group) {
		let index = -1
		for (const i in state.skillGroups) {
			if (state.skillGroups[i].id === group.id) {
				index = i
			}
		}

		if (index > -1) {
			store.commit('removeSkillGroup', index)
		}
	}
}

const mutations = {
	updateCalendarName(state, calendarName) {
		Vue.set(state, 'calendarName', calendarName)
	},
	updateAddUserCalendarEvent(state, addEvent) {
		Vue.set(state, 'addUserCalendarEvent', addEvent)
	},
	updateAnalystGroup(state, analystGroup) {
		Vue.set(state, 'analystGroup', analystGroup)
	},
	updateShiftAdminGroup(state, shiftAdminGroup) {
		Vue.set(state, 'shiftAdminGroup', shiftAdminGroup)
	},

	updateOrganizerName(state, organizerName) {
		Vue.set(state, 'organizerName', organizerName)
	},
	updateOrganizerEmail(state, organizerEmail) {
		Vue.set(state, 'organizerEmail', organizerEmail)
	},
	updateShiftTimezone(state, timezone) {
		Vue.set(state, 'timezone', timezone)
	},

	updateSkillGroups(state, skillGroups) {
		Vue.set(state, 'skillGroups', skillGroups)
	},

	updateShiftChangeSameType(store, shiftChangeSameType) {
		Vue.set(state, 'shiftChangeSameShiftType', shiftChangeSameType)
	},

	updateSettingsFetched(state, fetched) {
		Vue.set(state, 'settingsFetched', fetched)
	},

	addEmptyRowToSkillGroups(state, newId) {
		state.skillGroups.push({
			id: newId,
			name: '',
		})
	},
	removeSkillGroup(state, index) {
		state.skillGroups.splice(index, 1)
	}
}

export default { state, mutations, getters, actions }
