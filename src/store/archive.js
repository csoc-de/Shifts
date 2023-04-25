/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

import { showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Vue from 'vue'
import dayjs from 'dayjs'

const state = {
	timeRange: 6,
	startDate: 0,
	endDate: 0,
	archiveShifts: {},
}

const getters = {
	getArchiveTimeRange(state) {
		return state.timeRange
	},
	getShiftsForAnalystByShiftType: (state) => (analystId, shiftTypeId) => {
		if (analystId in state.archiveShifts) {
			if (shiftTypeId in state.archiveShifts[analystId]) {
				return state.archiveShifts[analystId][shiftTypeId]
			} else {
				return 0
			}
		} else {
			return 0
		}
	},
}

const actions = {
	setArchiveTimeRange(context, { timeRange, startDate, endDate }) {
		if (!startDate) {
			startDate = dayjs()
		}

		if (!endDate) {
			endDate = dayjs()
		}

		if (timeRange) {
			startDate = startDate.subtract(timeRange, 'month')
			context.commit('setArchiveTimeRange', timeRange)
		} else {
			context.commit('setArchiveTimeRange', 0)
		}

		context.commit('setArchiveStartAndEndTime', { startDate, endDate })
		context.dispatch('fetchCurrentArchiveData')
	},

	fetchCurrentArchiveData(context) {
		try {
			axios.get(generateUrl('/apps/shifts/getShiftsDataByTimeRange/' + context.state.startDate.format('YYYY-MM-DD') + '/' + context.state.endDate.format('YYYY-MM-DD'))).then(response => {
				context.commit('clearArchiveShifts')

				response.data.forEach(shift => {

					if (typeof shift.shift_type_id === 'string') {
						shift.shift_type_id = parseInt(shift.shift_type_id)
					}
					if (typeof shift.count === 'string') {
						shift.count = parseInt(shift.count)
					}

					context.commit('setArchiveShiftsForAnalyst', {
						analystId: shift.user_id,
						shiftTypeId: shift.shift_type_id,
						count: shift.count,
					})
				})
			}).catch(e => {
				console.error('Failed to fetch archive data:', e)
			})
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch archive data'))
		}
	}
}

const mutations = {
	setArchiveTimeRange(state, timeRange) {
		state.timeRange = timeRange
	},
	setArchiveStartAndEndTime(state, { startDate, endDate }) {
		state.startDate = startDate
		state.endDate = endDate
	},

	clearArchiveShifts(state) {
		Vue.set(state, 'archiveShifts', {})
	},
	setArchiveShiftsForAnalyst(state, { analystId, shiftTypeId, count }) {
		let obj = {}
		if (analystId in state.archiveShifts) {
			obj = state.archiveShifts[analystId]
		}
		obj[shiftTypeId] = count
		Vue.set(state.archiveShifts, analystId, obj)
	}
}

export default { state, mutations, getters, actions }
