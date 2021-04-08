import Vue from 'vue'
import Vuex from 'vuex'

import newShiftInstance from './newShiftInstance'

Vue.use(Vuex)

export default new Vuex.Store({
	modules: {
		newShiftInstance,
	},
})
