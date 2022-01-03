/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

import { showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const state = {
	currentShiftsData: [],
	currentFormat: null,
	archiveLoading: false,
	dates: [],
}

const mutations = {
	updateSelectedFormat(state, format) {
		state.currentFormat = format
	},
	updateShiftsData(state, data) {
		state.currentShiftsData = data
	},
	updateArchiveLoading(state, loading) {
		state.archiveLoading = loading
	},
	updateDates(state, dates) {
		state.dates = dates
	},
}

const getters = {
	archiveLoading(state) {
		return state.archiveLoading
	},
	currentShiftsData(state) {
		return state.currentShiftsData
	},
	currentDates(state) {
		return state.dates
	},
}

const actions = {
	async setupArchive({ dispatch, commit, rootState }) {
		try {
			commit('updateArchiveLoading', true)
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/getShiftsDataByTimeRange/1970-01-01/2100-01-01'))
			const allShifts = []
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = rootState.database.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				allShifts.push(shift)
			})
			const rows = []
			let currUserId = ''
			let currRow = null
			allShifts.forEach(shiftData => {
				if (currUserId !== shiftData.user_id) {
					if (currRow) {
						rows.push(currRow)
					}
					currRow = {}
					currUserId = shiftData.user_id
					currRow.userName = currUserId
				}
				currRow[shiftData.shift_type_id.toString()] = shiftData.num_shifts
			})
			this.items = rows
			commit('updateShiftsData', rows)
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
		}
		commit('updateArchiveLoading', false)
	},
	async fetchArchiveData({ state, dispatch, commit, rootState, getters }, dates) {
		try {
			const shiftResponse = await axios.get(generateUrl(`/apps/shifts/getShiftsDataByTimeRange/${dates[0]}/${dates[1]}`))
			const allShifts = []
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = rootState.database.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				allShifts.push(shift)
			})
			const rows = []
			let currUserId = ''
			let currRow = null
			allShifts.forEach(shiftData => {
				if (currUserId !== shiftData.user_id) {
					if (currRow) {
						rows.push(currRow)
					}
					currRow = {}
					currUserId = shiftData.user_id
					currRow.userName = currUserId
				}
				currRow[shiftData.shift_type_id.toString()] = shiftData.num_shifts
			})
			this.items = rows
			commit('updateShiftsData', rows)
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
		}
	},
}

export default { state, mutations, getters, actions }
