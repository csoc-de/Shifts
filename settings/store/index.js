/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

import Vue from 'vue'
import Vuex from 'vuex'

import settings from './settings'

Vue.use(Vuex)

const store = new Vuex.Store({
	modules: {
		settings,
	},
})

export default store
