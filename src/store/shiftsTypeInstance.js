/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const state = {
	id: undefined,
	name: '',
	description: '',
	startTimestamp: '',
	stopTimestamp: '',
	color: '',
	moRule: 0,
	tuRule: 0,
	weRule: 0,
	thRule: 0,
	frRule: 0,
	saRule: 0,
	soRule: 0,
	skillGroupId: 0,
	isWeekly: false,
}

const actions = {
	createNewShiftsType(context) {
		context.commit('setShiftsTypeInstanceId', undefined)
		context.commit('setShiftsTypeInstanceName', '')
		context.commit('setShiftsTypeInstanceDescription', '')
		context.commit('setShiftsTypeInstanceSkillGroupId', 0)
		context.commit('setShiftsTypeInstanceColor', '#5a5a5a')
		context.commit('setShiftsTypeInstanceStartTime', '')
		context.commit('setShiftsTypeInstanceStopTime', '')
		context.commit('setShiftsTypeInstanceWeekly', false)
		context.commit('setShiftsTypeInstanceMoRule', 0)
		context.commit('setShiftsTypeInstanceTuRule', 0)
		context.commit('setShiftsTypeInstanceWeRule', 0)
		context.commit('setShiftsTypeInstanceThRule', 0)
		context.commit('setShiftsTypeInstanceFrRule', 0)
		context.commit('setShiftsTypeInstanceSaRule', 0)
		context.commit('setShiftsTypeInstanceSoRule', 0)
	},
	editExistingShiftsType(context, shiftsType) {
		const start = new Date(shiftsType.startTimestamp)
		const stop = new Date(shiftsType.stopTimestamp)

		context.commit('setShiftsTypeInstanceId', shiftsType.id)
		context.commit('setShiftsTypeInstanceName', shiftsType.name)
		context.commit('setShiftsTypeInstanceDescription', shiftsType.description)
		context.commit('setShiftsTypeInstanceSkillGroupId', shiftsType.skillGroupId)
		context.commit('setShiftsTypeInstanceColor', shiftsType.color)
		context.commit('setShiftsTypeInstanceStartTime', start)
		context.commit('setShiftsTypeInstanceStopTime', stop)
		context.commit('setShiftsTypeInstanceWeekly', shiftsType.isWeekly)
		context.commit('setShiftsTypeInstanceMoRule', shiftsType.moRule)
		context.commit('setShiftsTypeInstanceTuRule', shiftsType.tuRule)
		context.commit('setShiftsTypeInstanceWeRule', shiftsType.weRule)
		context.commit('setShiftsTypeInstanceThRule', shiftsType.thRule)
		context.commit('setShiftsTypeInstanceFrRule', shiftsType.frRule)
		context.commit('setShiftsTypeInstanceSaRule', shiftsType.saRule)
		context.commit('setShiftsTypeInstanceSoRule', shiftsType.soRule)
	},

	saveCurrentShiftsType(context) {
		return new Promise((resolve, reject) => {
			let prom
			if (state.id) {
				prom = axios.put(generateUrl(`/apps/shifts/shiftsType/${state.id}`), {
					name: context.state.name,
					desc: context.state.description,
					startTimestamp: context.state.startTimestamp,
					stopTimestamp: context.state.stopTimestamp,
					color: context.state.color,
					moRule: context.state.moRule,
					tuRule: context.state.tuRule,
					weRule: context.state.weRule,
					thRule: context.state.thRule,
					frRule: context.state.frRule,
					saRule: context.state.saRule,
					soRule: context.state.soRule,
					skillGroupId: context.state.skillGroupId,
					isWeekly: context.state.isWeekly,
					deleted: false
				})
			} else {
				prom = axios.post(generateUrl('/apps/shifts/shiftsType'), {
					name: context.state.name,
					description: context.state.description,
					startTimestamp: context.state.startTimestamp,
					stopTimestamp: context.state.stopTimestamp,
					color: context.state.color,
					moRule: context.state.moRule,
					tuRule: context.state.tuRule,
					weRule: context.state.weRule,
					thRule: context.state.thRule,
					frRule: context.state.frRule,
					saRule: context.state.saRule,
					soRule: context.state.soRule,
					skillGroupId: context.state.skillGroupId,
					isWeekly: context.state.isWeekly,
				})
			}

			prom.then(() => {
				context.dispatch('updateShiftsTypes')
				resolve()
			}).catch((e) => {
				reject(e)
			})
		})
	},
	deleteShiftsType({ state, dispatch, getters }, shiftsType) {
		return new Promise((resolve, reject) => {
			shiftsType.deleted = true
			axios.put(generateUrl(`/apps/shifts/shiftsType/${shiftsType.id}`), shiftsType).then((response) => {
				if (response.status === 200) {
					resolve()
				} else {
					reject(new Error('Failed to delete shift: ' + response.statusText))
				}
			}).catch((e) => {
				reject(e)
			})
		})
	},

	setShiftsTypeInstanceName(context, name) {
		context.commit('setShiftsTypeInstanceName', name)
	},
	setShiftsTypeInstanceColor(context, color) {
		context.commit('setShiftsTypeInstanceColor', color)
	},
	setShiftsTypeInstanceSkillGroupId(context, skillGroup) {
		if (skillGroup) {
			context.commit('setShiftsTypeInstanceSkillGroupId', skillGroup.id)
		} else {
			context.commit('setShiftsTypeInstanceSkillGroupId', '0')
		}
	},
	setShiftsTypeInstanceStartTime(context, time) {
		context.commit('setShiftsTypeInstanceStartTime', time)
	},
	setShiftsTypeInstanceStopTime(context, time) {
		context.commit('setShiftsTypeInstanceStopTime', time)
	},
	setShiftsTypeInstanceWeekly(context, weekly) {
		context.commit('setShiftsTypeInstanceWeekly', weekly)
	},
	setShiftsTypeInstanceMoRule(context, rule) {
		context.commit('setShiftsTypeInstanceMoRule', rule)
	},
	setShiftsTypeInstanceTuRule(context, rule) {
		context.commit('setShiftsTypeInstanceTuRule', rule)
	},
	setShiftsTypeInstanceWeRule(context, rule) {
		context.commit('setShiftsTypeInstanceWeRule', rule)
	},
	setShiftsTypeInstanceThRule(context, rule) {
		context.commit('setShiftsTypeInstanceThRule', rule)
	},
	setShiftsTypeInstanceFrRule(context, rule) {
		context.commit('setShiftsTypeInstanceFrRule', rule)
	},
	setShiftsTypeInstanceSaRule(context, rule) {
		context.commit('setShiftsTypeInstanceSaRule', rule)
	},
	setShiftsTypeInstanceSoRule(context, rule) {
		context.commit('setShiftsTypeInstanceSoRule', rule)
	},
}

const mutations = {
	setShiftsTypeInstanceId(state, id) {
		state.id = id
	},
	setShiftsTypeInstanceName(state, name) {
		state.name = name
	},
	setShiftsTypeInstanceDescription(state, desc) {
		state.desc = desc
	},
	setShiftsTypeInstanceColor(state, color) {
		state.color = color
	},
	setShiftsTypeInstanceStartTime(state, start) {
		state.startTimestamp = start
	},
	setShiftsTypeInstanceStopTime(state, stop) {
		state.stopTimestamp = stop
	},
	setShiftsTypeInstanceMoRule(state, value) {
		state.moRule = value
	},
	setShiftsTypeInstanceTuRule(state, value) {
		state.tuRule = value
	},
	setShiftsTypeInstanceWeRule(state, value) {
		state.weRule = value
	},
	setShiftsTypeInstanceThRule(state, value) {
		state.thRule = value
	},
	setShiftsTypeInstanceFrRule(state, value) {
		state.frRule = value
	},
	setShiftsTypeInstanceSaRule(state, value) {
		state.saRule = value
	},
	setShiftsTypeInstanceSoRule(state, value) {
		state.soRule = value
	},
	setShiftsTypeInstanceSkillGroupId(state, skillGroupId) {
		state.skillGroupId = skillGroupId
	},
	setShiftsTypeInstanceWeekly(state, value) {
		state.isWeekly = value
	},
}

const getters = {
	shiftsTypeInstance(state) {
		return state.shiftsTypeInstance
	},
}

export default { state, mutations, getters, actions }
