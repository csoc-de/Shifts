import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import { deleteExistingCalendarObject } from '../services/calendarService'

const state = {
	allAnalysts: [],
	allShiftsChanges: [],
	allShiftsTypes: [],
	allShifts: [],
	isCurrentUserAdmin: false,
	currentUserId: null,
}

const mutations = {
	resetInstance(state) {
		state.allAnalysts = []
		state.allShiftsChanges = []
		state.allShiftsTypes = []
		state.allShifts = []
		state.isCurrentUserAdmin = false
		state.currentUserId = null
	},
}

const getters = {
	allAnalysts(state) {
		return state.allAnalysts
	},
	allShiftsChanges(state) {
		if (state.isCurrentUserAdmin) {
			return state.allShiftsChanges
		} else {
			this.getShiftsChangesByUserId()
		}
	},
	getShiftsChangesByUserId(state) {
		return state.allShiftsChanges.filter((shiftsChange) => {
			return shiftsChange.oldAnalystId === this.currentUserId || shiftsChange.newAnalystId === this.currentUserId
		})
	},
	allShiftsTypes(state) {
		return state.allShiftsTypes
	},
	allShifts(state) {
		return state.allShifts
	},
	isAdmin(state) {
		return state.isCurrentUserAdmin
	},
	currentUserId(state) {
		return state.currentUserId
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
			const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			const currentUserResponse = await axios.get(generateUrl('/apps/shifts/getCurrentUserId'))

			state.currentUserId = currentUserResponse.data
			state.isCurrentUserAdmin = isAdminResponse.data
			state.allAnalysts = analystsResponse.data
			state.allShiftsChanges = shiftsChangeResponse.data
			state.allShiftsTypes = shiftTypeResponse.data
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				state.allShifts.push(shift)
			})
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
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
			state.allShifts = newShifts
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts'))
		}
	},
	async updateShiftsTypes({ state, dispatch, commit }) {
		try {
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			state.allShiftsTypes = shiftTypeResponse.data
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts-types'))
		}
	},
	async updateShiftsChanges({ commit, state, getters }) {
		try {
			const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			state.allShiftsChanges = shiftsChangeResponse.data
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts-changes'))
		}
	},
	async deleteShift({ state, dispatch, getters }, shiftId) {
		try {
			const shift = getters.getShiftById(shiftId)
			const shiftsType = getters.getShiftsTypeById(shift.shiftTypeId)
			const analyst = getters.getAnalystById(shift.userId)
			await deleteExistingCalendarObject(shiftsType, shift, analyst)
			await axios.delete(generateUrl(`/apps/shifts/shifts/${shiftId}`))
			dispatch('updateShifts')
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not delete shift'))
		}
	},
}

export default { state, mutations, getters, actions }
