/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import dayjs from 'dayjs'
import dayOfYear from 'dayjs/plugin/dayOfYear'
import isBetween from 'dayjs/plugin/isBetween'
import 'dayjs/locale/de'
import Vue from 'vue'

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

	dailyShiftTypes: [],
	allAnalystShifts: {},
	currentShiftsData: {},
	currentOpenShiftsData: {},

	processedShiftChangeRequests: [],
	inProgressShiftChangeRequests: [],
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

	getDailyShiftTypes(state) {
		return state.dailyShiftTypes
	},

	hasAnalystMinShiftLevel: (state) => (uid, shiftTypeId) => {
		let shiftLevel = 0
		for (const i in state.allShiftsTypes) {
			if (state.allShiftsTypes[i].id === shiftTypeId) {
				shiftLevel = state.allShiftsTypes[i].skillGroupId
			}
		}

		for (const i in state.allAnalysts) {
			if (state.allAnalysts[i].uid === uid) {
				return parseInt(state.allAnalysts[i].skillGroup) >= shiftLevel
			}
		}
		return false
	},
	hasAnalystDailyShift: (state) => (uid, date, shiftTypeId) => {
		for (const i in state.currentShiftsData[uid].daily[date]) {
			if (state.currentShiftsData[uid].daily[date][i].shiftTypeId === shiftTypeId) {
				return true
			}
		}
		return false
	},
	hasAnalystWeeklyShift: (state) => (uid, weekNo, shiftTypeId) => {
		for (const i in state.currentShiftsData[uid].weekly[weekNo]) {
			if (state.currentShiftsData[uid].weekly[weekNo][i].shiftTypeId === shiftTypeId) {
				return true
			}
		}
		return false
	},
	getDailyShiftsForAnalyst: (state) => (uid, date) => {
		return state.currentShiftsData[uid].daily[date]
	},
	getAllDailyShiftsForAnalyst: (state) => (uid, date) => {
		return state.allAnalystShifts[uid].daily[date]
	},
	getWeeklyShiftsForAnalyst: (state) => (uid, week) => {
		return state.currentShiftsData[uid].weekly[week]
	},
	getAllWeeklyShiftsForAnalyst: (state) => (uid, date) => {
		if (state.allAnalystShifts[uid] && state.allAnalystShifts[uid].weekly && state.allAnalystShifts[uid].weekly[date.week()]) {
			const shifts = state.allAnalystShifts[uid].weekly[date.week()].find((shift) => {
				const d = dayjs(shift.date)
				return d.year() === date.year()
			})
			return shifts
		} else {
			return []
		}
	},
	getOpenDailyShifts: (state) => (date) => {
		return state.currentOpenShiftsData.daily[date]
	},
	getOpenWeeklyShifts: (state) => (week) => {
		return state.currentOpenShiftsData.weekly[week]
	},

	getProcessedShiftChangeRequests(state) {
		return state.processedShiftChangeRequests
	},
	getInProgressShiftChangeRequests(state) {
		return state.inProgressShiftChangeRequests
	},
}

const actions = {
	async setup({ state, dispatch, commit }) {
		try {
			commit('updateLoading', true)
			// const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			// const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			// const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			const currentUserResponse = await axios.get(generateUrl('/apps/shifts/getCurrentUserId'))

			await dispatch('updateShiftsTypes')

			commit('updateCurrentUser', currentUserResponse.data)
			commit('updateUserStatus', isAdminResponse.data)
			for (const i in analystsResponse.data) {
				if (!analystsResponse.data[i].email) {
					analystsResponse.data[i].email = 'N/A'
				}
			}
			commit('updateAllAnalysts', analystsResponse.data)
			// commit('updateAllShiftsTypes', shiftTypeResponse.data)

			await dispatch('fetchAllShifts')
			await dispatch('fetchCurrentShifts')
			await dispatch('fetchAllAnalystShifts')

			await dispatch('fetchShiftsChanges')

			await dispatch('triggerUnassignedShifts')
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
		}

		commit('updateLoading', false)
	},

	triggerUnassignedShifts(context) {
		if (state.isCurrentUserAdmin) {
			axios.get(generateUrl('/apps/shifts/triggerUnassignedShifts')).then(() => {
				context.dispatch('updateShifts')
			})
		}
	},

	updateShifts(context) {
		return context.dispatch('fetchAllShifts').then(() => {
			context.dispatch('fetchCurrentShifts')
			context.dispatch('fetchAllAnalystShifts')
		})
	},
	// TODO: Will be replaced with fetch for current time range
	async fetchAllShifts(context) {
		const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
		const allShifts = []
		shiftResponse.data.forEach(shift => {
			if (typeof shift.shiftTypeId === 'string') {
				shift.shiftTypeId = parseInt(shift.shiftTypeId)
			}
			shift.shiftsType = state.allShiftsTypes.find((shiftType) => shiftType.id === shift.shiftTypeId)
			if (shift.shiftsType !== undefined) {
				allShifts.push(shift)
			}
		})
		context.commit('updateAllShifts', allShifts)
	},
	async updateShift({ state, dispatch, commit }, newShift) {
		return new Promise((resolve, reject) => {
			axios.put(generateUrl(`/apps/shifts/shifts/${newShift.id}`), newShift).then(() => {
				resolve()
			}).catch((e) => {
				console.error('Could not update shift:', e)
				reject(e)
			})
		})
	},

	requestShiftChange(context, changeRequest) {
		return new Promise((resolve, reject) => {
			axios.post(generateUrl('/apps/shifts/shiftsChange'), changeRequest).then((data) => {
				resolve()
			}).catch((e) => {
				console.error('Could not request shift change:', e)
				reject(e)
			})
		})
	},
	fetchShiftsChanges(context) {
		return new Promise((resolve, reject) => {
			axios.get(generateUrl('/apps/shifts/shiftsChange')).then((response) => {
				if (response.status === 200 && response.data) {
					return response.data
				} else {
					reject(new Error('Failed to fetch shift changes: ' + response.statusText))
				}
			}).then((response) => {

				// TODO: In the future, the backend will return the shifts object
				const shiftsChanges = []
				response.forEach(shiftsChange => {
					shiftsChange.oldShift = context.state.allShifts.find((shift) => shift.id.toString() === shiftsChange.oldShiftsId.toString())
					shiftsChange.newShift = context.state.allShifts.find((shift) => shift.id.toString() === shiftsChange.newShiftsId.toString())
					shiftsChange.adminApproval = shiftsChange.adminApproval === '1'
					shiftsChange.analystApproval = shiftsChange.analystApproval === '1'

					if (typeof shiftsChange.newShiftsId === 'string') {
						shiftsChange.newShiftsId = parseInt(shiftsChange.newShiftsId)
					}
					if (typeof shiftsChange.oldShiftsId === 'string') {
						shiftsChange.oldShiftsId = parseInt(shiftsChange.oldShiftsId)
					}
					if (typeof shiftsChange.type === 'string') {
						shiftsChange.type = parseInt(shiftsChange.type)
					}

					shiftsChanges.push(shiftsChange)
				})

				context.commit('setInProgressShiftChangeRequests', shiftsChanges.filter((changeRequest) => {
					return !(changeRequest.adminApproval && changeRequest.analystApproval)
				}))
				context.commit('setProcessedShiftChangeRequests', shiftsChanges.filter((changeRequest) => {
					return changeRequest.adminApproval && changeRequest.analystApproval
				}))
				resolve()
			}).catch((e) => {
				reject(e)
			})
		})
	},
	updateShiftChangeRequest(context, changeRequest) {
		return new Promise((resolve, reject) => {
			axios.put(generateUrl(`/apps/shifts/shiftsChange/${changeRequest.id}`), changeRequest).then((response) => {
				if (response.status === 200) {
					resolve()
				} else {
					reject(new Error('Failed to update shift change request: ' + response.statusText))
				}
			}).catch((e) => {
				reject(e)
			})
		})
	},
	deleteShiftChangeRequest(context, changeRequest) {
		return new Promise((resolve, reject) => {
			axios.delete(generateUrl(`/apps/shifts/shiftsChange/${changeRequest.id}`)).then((response) => {
				if (response.status === 200) {
					resolve()
				} else {
					reject(new Error('Failed to delete shift change request: ' + response.statusText))
				}
			}).catch((e) => {
				reject(e)
			})
		})
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

	async updateShiftsTypes({ state, dispatch, commit }) {
		try {
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const daily = []
			for (const i in shiftTypeResponse.data) {
				const shift = shiftTypeResponse.data[i]
				shift.isWeekly = (shift.isWeekly === '1' || shift.isWeekly === 1)
				if (typeof shift.deleted === 'string') {
					shift.deleted = parseInt(shift.deleted)
				}
				if (typeof shift.skillGroupId === 'string') {
					shift.skillGroupId = parseInt(shift.skillGroupId)
				}
				if (typeof shift.moRule === 'string') {
					shift.moRule = parseInt(shift.moRule)
				}
				if (typeof shift.tuRule === 'string') {
					shift.tuRule = parseInt(shift.tuRule)
				}
				if (typeof shift.weRule === 'string') {
					shift.weRule = parseInt(shift.weRule)
				}
				if (typeof shift.thRule === 'string') {
					shift.thRule = parseInt(shift.thRule)
				}
				if (typeof shift.frRule === 'string') {
					shift.frRule = parseInt(shift.frRule)
				}
				if (typeof shift.saRule === 'string') {
					shift.saRule = parseInt(shift.saRule)
				}
				if (typeof shift.soRule === 'string') {
					shift.soRule = parseInt(shift.soRule)
				}

				shiftTypeResponse.data[i].rules = [shift.moRule, shift.tuRule, shift.weRule, shift.thRule, shift.frRule, shift.saRule, shift.soRule]
				if (!shiftTypeResponse.data[i].isWeekly) {
					daily.push(shiftTypeResponse.data[i])
				}
			}
			commit('updateDailyShiftsTypes', daily)
			commit('updateAllShiftsTypes', shiftTypeResponse.data)
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts-types'))
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
	async deleteRequest({ state, dispatch, getters }, shiftsChange) {
		try {
			await axios.delete(generateUrl(`/apps/shifts/shiftsType/${shiftsChange.id}`))
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not delete request'))
		}
	},
	synchronizeCalendar(context) {
		return new Promise((resolve, reject) => {
			axios.patch(generateUrl('/apps/shifts/shiftsCalendar')).then(() => {
				resolve()
			}).catch((e) => {
				reject(e)
			})
		})
	},

	/*
		New functions
	 */
	setDisplayDateToday(context) {
		context.commit('setDisplayDate', dayjs())
		context.dispatch('fetchCurrentShifts')
	},
	prevDisplayDate(context) {
		const newDate = context.state.currentDateDisplayed.add(-1, context.state.currentDateDisplayFormat)
		context.commit('setDisplayDate', newDate)
		context.dispatch('fetchCurrentShifts')
	},
	nextDisplayDate(context) {
		const newDate = context.state.currentDateDisplayed.add(1, context.state.currentDateDisplayFormat)
		context.commit('setDisplayDate', newDate)
		context.dispatch('fetchCurrentShifts')
	},
	setDisplayedDateFormat(context, newFormat) {
		const validFormats = ['week', 'month']
		if (validFormats.includes(newFormat)) {
			context.commit('setDisplayedDateFormat', newFormat)
			context.dispatch('fetchCurrentShifts')
		} else {
			console.error('Invalid date format:', newFormat)
		}
	},

	async requestAdminStatus() {
		try {
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			return isAdminResponse.data
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch data'))
		}
	},

	fetchAllAnalystShifts(context) {
		const shifts = {}

		// Add every analyst to the dictionary
		for (const i in context.state.allAnalysts) {
			shifts[context.state.allAnalysts[i].uid] = {
				daily: {},
				weekly: {},
			}
		}

		for (const i in context.state.allShifts) {
			if (shifts[context.state.allShifts[i].userId] === undefined) {
				continue
			}

			if (context.state.allShifts[i].shiftsType.isWeekly) {
				// Create empty list if weekly shift is undefined
				const weekNo = dayjs(context.state.allShifts[i].date).week()
				if (shifts[context.state.allShifts[i].userId].weekly[weekNo] === undefined) {
					shifts[context.state.allShifts[i].userId].weekly[weekNo] = []
				}
				shifts[context.state.allShifts[i].userId].weekly[weekNo].push(context.state.allShifts[i])
			} else {
				// Create empty list if daily shift is undefined
				if (shifts[context.state.allShifts[i].userId].daily[context.state.allShifts[i].date] === undefined) {
					shifts[context.state.allShifts[i].userId].daily[context.state.allShifts[i].date] = []
				}
				shifts[context.state.allShifts[i].userId].daily[context.state.allShifts[i].date].push(context.state.allShifts[i])
			}
		}
		context.commit('setAllAnalystShifts', shifts)
	},

	fetchCurrentShifts(context) {
		let startDate = context.state.currentDateDisplayed.startOf(context.state.currentDateDisplayFormat)
		const endDate = context.state.currentDateDisplayed.endOf(context.state.currentDateDisplayFormat)

		// Filter shifts for the current displayed date
		const shifts = context.state.allShifts.filter((shift) => {
			const shiftDate = dayjs(shift.date)
			return shiftDate.isBetween(startDate, endDate, null, '[)')
		})

		// Create list with every date in range startDate to endDate
		const dates = []
		let numDays = endDate.diff(startDate, 'day', true)
		while (numDays > 0) {
			dates.push(startDate)
			startDate = startDate.add(1, 'day')
			numDays--
		}

		// Create dictionary with open shifts
		const openShifts = {
			daily: {},
			weekly: {},
		}
		for (const j in dates) {
			openShifts.daily[dates[j].format('YYYY-MM-DD')] = []
			if (openShifts.weekly[dates[j].week()] === undefined) {
				openShifts.weekly[dates[j].week()] = []
			}
		}
		for (const i in context.state.allShiftsTypes) {
			let currentWeek = 0
			for (const j in dates) {
				if (currentWeek !== dates[j].week() && context.state.allShiftsTypes[i].isWeekly) {
					currentWeek = dates[j].week()

					for (let x = 0; x < context.state.allShiftsTypes[i].moRule; x++) {
						openShifts.weekly[currentWeek].push(context.state.allShiftsTypes[i])
					}
				} else if (!context.state.allShiftsTypes[i].isWeekly) {
					const arrLen = context.state.allShiftsTypes[i].rules.length
					const ruleIndex = (dates[j].day() + arrLen - 1) % arrLen
					for (let x = 0; x < context.state.allShiftsTypes[i].rules[ruleIndex]; x++) {
						openShifts.daily[dates[j].format('YYYY-MM-DD')].push(context.state.allShiftsTypes[i])
					}
				}
			}
		}

		// Dictionary for displayed shifts, split into daily and weekly shifts
		const displayedShifts = {
			'-1': {
				daily: {},
				weekly: {},
			}
		}

		// Add every analyst to the dictionary
		for (const i in context.state.allAnalysts) {
			displayedShifts[context.state.allAnalysts[i].uid] = {
				daily: {},
				weekly: {},
			}
		}

		// Sort shifts into dictionary
		for (const i in shifts) {
			if (shifts[i].shiftsType.isWeekly) {
				// Create empty list if weekly shift is undefined
				const weekNo = dayjs(shifts[i].date).week()
				if (displayedShifts[shifts[i].userId].weekly[weekNo] === undefined) {
					displayedShifts[shifts[i].userId].weekly[weekNo] = []
				}

				displayedShifts[shifts[i].userId].weekly[weekNo].push(shifts[i])

				if (shifts[i].userId !== -1 && shifts[i].userId !== '-1' && openShifts.weekly[weekNo]) {
					let index = -1
					for (let x = 0; x < openShifts.weekly[weekNo].length; x++) {
						if (openShifts.weekly[weekNo][x].id === shifts[i].shiftTypeId) {
							index = x
							break
						}
					}

					if (index > -1) {
						openShifts.weekly[weekNo].splice(index, 1)
					}
				}
			} else {
				// Create empty list if daily shift is undefined
				if (displayedShifts[shifts[i].userId].daily[shifts[i].date] === undefined) {
					displayedShifts[shifts[i].userId].daily[shifts[i].date] = []
				}
				displayedShifts[shifts[i].userId].daily[shifts[i].date].push(shifts[i])
				if (shifts[i].userId !== -1 && shifts[i].userId !== '-1' && openShifts.daily[shifts[i].date]) {
					let index = -1
					for (let x = 0; x < openShifts.daily[shifts[i].date].length; x++) {
						if (openShifts.daily[shifts[i].date][x].id === shifts[i].shiftTypeId) {
							index = x
							break
						}
					}

					if (index > -1) {
						openShifts.daily[shifts[i].date].splice(index, 1)
					}
				}
			}
		}
		context.commit('setDisplayedShifts', displayedShifts)
		context.commit('setDisplayedOpenShifts', openShifts)
	},
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
		state.allAnalystShifts = {}
		state.currentDateDisplayed = dayjs()
		state.currentDateDisplayFormat = 'week'
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
	updateDailyShiftsTypes(state, shiftTypes) {
		state.dailyShiftTypes = shiftTypes
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

	setDisplayDate(state, newDate) {
		state.currentDateDisplayed = newDate
	},
	setDisplayedDateFormat(state, newFormat) {
		state.currentDateDisplayFormat = newFormat
	},

	setAllAnalystShifts(state, shifts) {
		state.allAnalystShifts = shifts
	},
	setDisplayedShifts(state, shifts) {
		state.currentShiftsData = shifts
	},
	setDisplayedOpenShifts(state, shifts) {
		state.currentOpenShiftsData = shifts
	},
	setRequestedShiftChanges(state, changeRequest) {
		Vue.set(state, 'allShiftsChanges', changeRequest)
	},
	setInProgressShiftChangeRequests(state, changeRequests) {
		Vue.set(state, 'inProgressShiftChangeRequests', changeRequests)
	},
	setProcessedShiftChangeRequests(state, changeRequests) {
		Vue.set(state, 'processedShiftChangeRequests', changeRequests)
	}
}

export default { state, mutations, getters, actions }
