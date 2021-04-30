import Vue from 'vue'
import Vuex from 'vuex'

import newShiftInstance from './newShiftInstance'
import shiftsTypeInstance from './shiftsTypeInstance'
import database from './database'

Vue.use(Vuex)

export default new Vuex.Store({
	modules: {
		newShiftInstance,
		shiftsTypeInstance,
		database,
	},
})
