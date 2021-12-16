/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

const state = {
	shiftsTypeInstance: {
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
	},
}

const mutations = {
	changeName(state, name) {
		state.shiftsTypeInstance.name = name
	},
	changeDesc(state, desc) {
		state.shiftsTypeInstance.desc = desc
	},
	changeColor(state, color) {
		state.shiftsTypeInstance.color = color
	},
	changeStart(state, start) {
		state.shiftsTypeInstance.startTimestamp = start
	},
	changeStop(state, stop) {
		state.shiftsTypeInstance.stopTimestamp = stop
	},
	changeMoRule(state, value) {
		state.shiftsTypeInstance.moRule = value
	},
	changeTuRule(state, value) {
		state.shiftsTypeInstance.tuRule = value
	},
	changeWeRule(state, value) {
		state.shiftsTypeInstance.weRule = value
	},
	changeThRule(state, value) {
		state.shiftsTypeInstance.thRule = value
	},
	changeFrRule(state, value) {
		state.shiftsTypeInstance.frRule = value
	},
	changeSaRule(state, value) {
		state.shiftsTypeInstance.saRule = value
	},
	changeSoRule(state, value) {
		state.shiftsTypeInstance.soRule = value
	},
	changeSkillGroupId(state, skillGroup) {
		if (skillGroup) {
			state.shiftsTypeInstance.skillGroupId = skillGroup.id
		} else {
			state.shiftsTypeInstance.skillGroupId = '0'
		}
	},
	changeIsWeekly(state, value) {
		state.shiftsTypeInstance.isWeekly = value
	},
}

const getters = {
	shiftsTypeInstance(state) {
		return state.shiftsTypeInstance
	},
}

const actions = {
	createNewShiftsType({ state, dispatch, commit }) {
		const newShiftsTypeInstance = {
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
		state.shiftsTypeInstance = newShiftsTypeInstance
		return newShiftsTypeInstance
	},
	editExistingShiftsType({ state, dispatch, commit }, shiftsType) {
		shiftsType.isWeekly = shiftsType.isWeekly === '1'
		state.shiftsTypeInstance = shiftsType
	},
	async saveCurrentShiftsType({ state, dispatch, commit }) {
		try {
			state.shiftsTypeInstance.deleted = false
			if (state.shiftsTypeInstance.id) {
				await axios.put(generateUrl(`/apps/shifts/shiftsType/${state.shiftsTypeInstance.id}`), state.shiftsTypeInstance)
			} else {
				await axios.post(generateUrl('/apps/shifts/shiftsType'), state.shiftsTypeInstance)
			}
			dispatch('updateShiftsTypes')
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not save the shiftType'))
		}
	},
}

export default { state, mutations, getters, actions }
