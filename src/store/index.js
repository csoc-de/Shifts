import Vue from 'vue'
import Vuex from 'vuex'

import newShiftInstance from './newShiftInstance'
import shiftsTypeInstance from './shiftsTypeInstance'
import database from './database'
import settings from './settings'

Vue.use(Vuex)

const store = new Vuex.Store({
	modules: {
		newShiftInstance,
		shiftsTypeInstance,
		database,
		settings,
	},
})

export default store
