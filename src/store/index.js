import Vue from 'vue'
import Vuex from 'vuex'

import newShiftInstance from './newShiftInstance'
import shiftsTypeInstance from './shiftsTypeInstance'
import database from './database'
import settings from './settings'
import archive from './archive'

Vue.use(Vuex)

const store = new Vuex.Store({
	modules: {
		newShiftInstance,
		shiftsTypeInstance,
		database,
		settings,
		archive,
	},
})

export default store
