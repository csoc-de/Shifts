/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import dayjs from 'dayjs'
import dayOfYear from 'dayjs/plugin/dayOfYear'
import isBetween from 'dayjs/plugin/isBetween'
import { syncAll, syncAllShiftsChanges } from '../services/calendarService'
import 'dayjs/locale/de'

dayjs.extend(dayOfYear)
dayjs.extend(isBetween)
dayjs.locale('de')

const state = {
	loading: false,
	allAnalysts: [],
	allShiftsChanges: [],
	allShiftsTypes: [],
	allShifts: [],
	displayedShifts: [],
	isCurrentUserAdmin: false,
	currentUserId: null,
	currentDateDisplayed: dayjs(),
	currentDateDisplayFormat: 'week',
}

const mutations = {
	resetInstance(state) {
		state.allAnalysts = []
		state.allShiftsChanges = []
		state.allShiftsTypes = []
		state.allShifts = []
		state.displayedShifts = []
		state.isCurrentUserAdmin = false
		state.currentUserId = null
		state.currentDateDisplayed = dayjs()
		state.currentDateDisplayFormat = 'week'
	},
	updateDisplayedDate(state, newDate) {
		state.currentDateDisplayed = newDate
	},
	updateDisplayedDateFormat(state, newFormat) {
		state.currentDateDisplayFormat = newFormat
	},
	updateCurrentUser(state, currentUserId) {
		state.currentUserId = currentUserId
	},
	updateUserStatus(state, status) {
		state.isCurrentUserAdmin = status
	},
	updateAllShifts(state, allShifts) {
		state.allShifts = allShifts
	},
	updateAllShiftsTypes(state, allShiftsTypes) {
		state.allShiftsTypes = allShiftsTypes
	},
	updateAllAnalysts(state, allAnalysts) {
		state.allAnalysts = allAnalysts
	},
	updateAllShiftsChanges(state, allShiftsChanges) {
		state.allShiftsChanges = allShiftsChanges
	},
	updateLoading(state, loading) {
		state.loading = loading
	},
}

const getters = {
	loading(state) {
		return state.loading
	},
	allAnalysts(state) {
		return state.allAnalysts
	},
	allShiftsChanges(state, getters) {
		if (state.isCurrentUserAdmin) {
			return state.allShiftsChanges
		} else {
			return getters.getShiftsChangesByUserId
		}
	},
	getShiftsChangesByUserId(state, getters) {
		return state.allShiftsChanges.filter((shiftsChange) => {
			return shiftsChange.oldAnalystId === getters.currentUserId || shiftsChange.newAnalystId === getters.currentUserId
		})
	},
	allShiftsTypes(state) {
		return state.allShiftsTypes
	},
	allShifts(state) {
		return state.allShifts
	},
	displayedShifts(state) {
		const currentDisplayDate = state.currentDateDisplayed
		const start = currentDisplayDate.startOf(state.currentDateDisplayFormat)
		const end = currentDisplayDate.endOf(state.currentDateDisplayFormat)
		return state.allShifts.filter((shift) => {
			const shiftDate = dayjs(shift.date)
			return shiftDate.isBetween(start, end, null, '[)')
		})
	},
	isAdmin(state) {
		return state.isCurrentUserAdmin
	},
	currentUserId(state) {
		return state.currentUserId
	},
	currentDateDisplayed(state) {
		return state.currentDateDisplayed
	},
	currentDateDisplayFormat(state) {
		return state.currentDateDisplayFormat
	},
	getAnalystById: (state) => (id) => {
		return state.allAnalysts.find(analyst => analyst.uid.toString() === id)
	},
	getShiftById: (state) => (id) => {
		return state.allShifts.find(shift => shift.id.toString() === id)
	},
	getShiftsTypeById: (state) => (id) => {
		return state.allShiftsTypes.find(shiftsType => shiftsType.id.toString() === id)
	},
}

const actions = {
	async setup({ state, dispatch, commit }) {
		try {
			commit('updateLoading', true)
			const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			const currentUserResponse = await axios.get(generateUrl('/apps/shifts/getCurrentUserId'))

			commit('updateCurrentUser', currentUserResponse.data)
			commit('updateUserStatus', isAdminResponse.data)
			commit('updateAllAnalysts', analystsResponse.data)
			commit('updateAllShiftsChanges', shiftsChangeResponse.data)
			commit('updateAllShiftsTypes', shiftTypeResponse.data)
			const allShifts = []
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				allShifts.push(shift)
			})
			commit('updateAllShifts', allShifts)
			if (state.isCurrentUserAdmin) {
				axios.get(generateUrl('/apps/shifts/triggerUnassignedShifts')).then(() => dispatch('updateShifts'))
			}
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
		}

		commit('updateLoading', false)
	},
	async requestAdminStatus({ state, dispatch, commit }) {
		try {
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			return isAdminResponse.data
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
		}
	},
	async updateShift({ state, dispatch, commit }, newShift) {
		try {
			await axios.put(generateUrl(`/apps/shifts/shifts/${newShift.id}`), newShift)
			dispatch('updateShifts')
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts'))
		}
	},
	async updateShifts({ state, dispatch, commit }) {
		try {
			const newShifts = []
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				newShifts.push(shift)
			})
			commit('updateAllShifts', newShifts)
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts'))
		}
	},
	async updateShiftsTypes({ state, dispatch, commit }) {
		try {
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			commit('updateAllShiftsTypes', shiftTypeResponse.data)
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts-types'))
		}
	},
	async updateShiftsChanges({ commit, state, getters }) {
		try {
			const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			commit('updateAllShiftsChanges', shiftsChangeResponse.data)
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts-changes'))
		}
	},
	async deleteShift({ state, dispatch, getters }, shift) {
		try {
			await axios.delete(generateUrl(`/apps/shifts/shifts/${shift.id}`))
			dispatch('updateShifts')
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not delete shift'))
		}
	},
	async deleteShiftsType({ state, dispatch, getters }, shiftsType) {
		try {
			shiftsType.deleted = true
			await axios.put(generateUrl(`/apps/shifts/shiftsType/${shiftsType.id}`), shiftsType)
			dispatch('updateShiftsTypes')
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not delete shiftsType'))
		}
	},
	async syncCalendar({ state, dispatch, getters }) {
		try {
			const changesResponse = await axios.get(generateUrl('/apps/shifts/shiftsCalendarChange'))
			const changes = []
			for (const change of changesResponse.data) {
				change.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id.toString() === change.shiftTypeId)
				if (change.shiftsType === undefined && change.isDone === '0') {
					const shiftsTypeResponse = await axios.get(generateUrl(`/apps/shifts/shiftsType/${change.shiftTypeId}`))
					change.shiftsType = shiftsTypeResponse.data
				}
				changes.push(change)
			}
			if (changes.some(change => change.isDone === '0')) {
				await syncAllShiftsChanges(changes.filter(change => change.isDone === '0'), state.allShiftsTypes, state.allAnalysts)
			} else if (changes.length === 0) {
				await syncAll(state.allShifts, state.allShiftsTypes, state.allAnalysts)
			}
			dispatch('updateShifts')
		} catch (e) {
			console.error(e)
			showError(t('shifts', ' Could not update Calendar'))
		}
	},
}

export default { state, mutations, getters, actions }
