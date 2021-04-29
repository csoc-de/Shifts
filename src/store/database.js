import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import dayjs from 'dayjs'
import dayOfYear from 'dayjs/plugin/dayOfYear'
import isBetween from 'dayjs/plugin/isBetween'
import { syncAllAssignedShifts } from '../services/calendarService'
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
	assignedShifts: [],
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
		state.assignedShifts = []
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
	assignedShifts(state) {
		return state.assignedShifts
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
			state.loading = true
			const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			const currentUserResponse = await axios.get(generateUrl('/apps/shifts/getCurrentUserId'))
			const assignedShiftsResponse = await axios.get(generateUrl('/apps/shifts/getAssignedShifts'))

			state.currentUserId = currentUserResponse.data
			state.isCurrentUserAdmin = isAdminResponse.data
			state.allAnalysts = analystsResponse.data
			state.allShiftsChanges = shiftsChangeResponse.data
			state.allShiftsTypes = shiftTypeResponse.data
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				state.allShifts.push(shift)
			})
			assignedShiftsResponse.data.forEach(shift => {
				shift.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				state.assignedShifts.push(shift)
			})
			if (state.isCurrentUserAdmin) {
				axios.get(generateUrl('/apps/shifts/triggerUnassignedShifts')).then(() => dispatch('updateShifts'))
			}
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
		}

		state.loading = false
	},
	async updateShifts({ state, dispatch, commit }) {
		try {
			const newShifts = []
			const newAssignedShifts = []
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const assignedShiftResponse = await axios.get(generateUrl('/apps/shifts/getAssignedShifts'))
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				newShifts.push(shift)
			})
			assignedShiftResponse.data.forEach(shift => {
				shift.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				newAssignedShifts.push(shift)
			})
			state.allShifts = newShifts
			state.assignedShifts = newAssignedShifts
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
			shift.userId = '-1'
			await axios.put(generateUrl(`/apps/shifts/shifts/${shiftId}`), shift)
			dispatch('updateShifts')
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not delete shift'))
		}
	},
	async syncCalendar({ state, dispatch, getters }) {
		await syncAllAssignedShifts(state.assignedShifts, state.allShiftsTypes, state.allAnalysts)
	},
}

export default { state, mutations, getters, actions }
