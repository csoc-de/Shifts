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
	changeRule(state, rule, value) {
		switch (rule) {
		case 'mo':
			state.shiftsTypeInstance.moRule = value
			break
		case 'tu':
			state.shiftsTypeInstance.tuRule = value
			break
		case 'we':
			state.shiftsTypeInstance.weRule = value
			break
		case 'th':
			state.shiftsTypeInstance.thRule = value
			break
		case 'fr':
			state.shiftsTypeInstance.frRule = value
			break
		case 'sa':
			state.shiftsTypeInstance.saRule = value
			break
		case 'so':
			state.shiftsTypeInstance.soRule = value
			break
		}
	},
	changeSkillGroupId(state, skillGroup) {
		console.log(state.shiftsTypeInstance)
		console.log(skillGroup)
		if (skillGroup) {
			state.shiftsTypeInstance.skillGroupId = skillGroup.id
		} else {
			state.shiftsTypeInstance.skillGroupId = '0'
		}
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
		}
		state.shiftsTypeInstance = newShiftsTypeInstance
		return newShiftsTypeInstance
	},
	editExistingShiftsType({ state, dispatch, commit }, shiftsType) {
		state.shiftsTypeInstance = shiftsType
	},
	async saveCurrentShiftsType({ state, dispatch, commit }) {
		try {
			if (state.shiftsTypeInstance.id) {
				await axios.put(generateUrl(`/apps/shifts/shiftsType/${state.shiftsTypeInstance.id}`), state.shiftsTypeInstance)
			} else {
				console.log(state.shiftsTypeInstance)
				await axios.post(generateUrl('/apps/shifts/shiftsType'), state.shiftsTypeInstance)
			}
			dispatch('updateShiftsTypes')
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not create the shiftType'))
		}
	},
}

export default { state, mutations, getters, actions }
