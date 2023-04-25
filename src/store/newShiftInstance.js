/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

import { showError, showWarning } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const state = {
	newShiftInstance: {
		analysts: [],
		shiftsType: '',
		dates: [],
	},
}

const mutations = {
	resetNewShiftInstance(state) {
		state.newShiftInstance.analysts = []
		state.dates = []
	},
	addAnalyst(state, analyst) {
		state.newShiftInstance.analysts.push(analyst)
	},
	removeAnalyst(state, userId) {
		const index = state.newShiftInstance.analysts.findIndex((analyst) => {
			return analyst.userId === userId
		})
		if (index !== -1) {
			state.newShiftInstance.analysts.splice(index, 1)
		}
	},
	changeShiftsType(state, shiftsType) {
		state.newShiftInstance.analysts = []
		state.newShiftInstance.shiftsType = shiftsType
	},
}

const getters = {
	getCurrentShiftsType(state) {
		return state.newShiftInstance.shiftsType
	},
}

const actions = {
	async saveNewShift({ state, dispatch, commit, rootState }) {
		const allShifts = rootState.database.allShifts
		const newShiftInstance = state.newShiftInstance
		if (newShiftInstance.analysts && newShiftInstance.dates && newShiftInstance.shiftsType) {
			try {
				await Promise.all(newShiftInstance.analysts.map(async (analyst) => {
					const analystId = analyst.userId
					const shiftTypeId = newShiftInstance.shiftsType.id
					const newShifts = newShiftInstance.dates.map((date) => {
						return {
							analystId,
							shiftTypeId,
							date,
							oldAnalystId: analystId,
							saveChanges: true
						}
					})
					await Promise.all(newShifts.map(async (newShift) => {
						const exists = allShifts.find((shift) => {
							return shift.userId === '-1'
								&& newShift.date === shift.date
								&& newShift.shiftTypeId.toString() === shift.shiftTypeId
						})
						if (exists === undefined) {
							const response = await axios.post(generateUrl('/apps/shifts/shifts'), newShift)
							if (response.data && response.data.date !== newShift.date) {
								await axios.put(generateUrl(`/apps/shifts/shifts/${response.data.id}`), newShift)
							}
						} else {
							newShift.id = exists.id
							await axios.put(generateUrl(`/apps/shifts/shifts/${newShift.id}`), newShift)
						}
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

	createNewShift(context, shift) {
		return new Promise((resolve, reject) => {
			axios.post(generateUrl('/apps/shifts/shifts'), shift).then(() => {
				resolve()
			}).catch((e) => {
				if (e.response) {
					reject(e.response)
				} else {
					reject(e)
				}
			})
		})
	}
}

export default { state, mutations, getters, actions }
