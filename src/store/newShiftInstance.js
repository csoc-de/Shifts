import { getYYYYMMDDFromDate } from '../utils/date'
import { showError, showWarning } from '@nextcloud/dialogs'
import { saveCalendarObjectFromNewShift } from '../services/calendarService'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const state = {
	newShiftInstance: {
		analysts: [],
		shiftsType: '',
		dates: [getYYYYMMDDFromDate(new Date())],
	},
}

const mutations = {
	resetNewShiftInstance(state) {
		state.newShiftInstance = {
			analysts: [],
			shiftsType: '',
			dates: [getYYYYMMDDFromDate(new Date())],
		}
	},
	addAnalyst(state, analyst) {
		state.newShiftInstance.analysts.push(analyst)
	},
	removeAnalyst(state, userId) {
		const index = state.newShiftInstance.analysts.findIndex((analyst) => {
			return analyst.userId === userId
		})
		console.log(index)
		if (index !== -1) {
			state.newShiftInstance.analysts.splice(index, 1)
		}
	},
	changeShiftsType(state, shiftsType) {
		state.newShiftInstance.shiftsType = shiftsType
	},
}

const getters = {

}

const actions = {
	async saveNewShift({ state, dispatch, commit }) {
		const newShiftInstance = state.newShiftInstance
		if (newShiftInstance.analysts && newShiftInstance.dates && newShiftInstance.shiftsType) {
			try {
				await saveCalendarObjectFromNewShift(newShiftInstance)
				await Promise.all(newShiftInstance.analysts.map(async(analyst) => {
					const analystId = analyst.userId
					const shiftTypeId = newShiftInstance.shiftsType.id
					const newShifts = newShiftInstance.dates.map((date) => {
						return {
							analystId,
							shiftTypeId,
							date,
						}
					})
					await Promise.all(newShifts.map(async(newShift) => {
						await axios.post(generateUrl('/apps/shifts/shifts'), newShift)
					}))
				}))
				commit('resetNewShiftInstance')
				dispatch('updateShifts')
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not create the shift'))
			}
		} else {
			console.log('No Name for ShiftType')
			showWarning(t('shifts', 'No Analysts or Dates for Shift given'))
		}
	},
}

export default { state, mutations, getters, actions }
