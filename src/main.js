import 'core-js/stable'

import Vue from 'vue'
import App from './App'
import router from './router'
import ClickOutside from 'vue-click-outside'
import VueClipboard from 'vue-clipboard2'
import VTooltip from 'v-tooltip'
import VueShortKey from 'vue-shortkey'

Vue.directive('ClickOutside', ClickOutside)
Vue.use(VTooltip)
Vue.use(VueClipboard)
Vue.use(VueShortKey, { prevent: ['input', 'textarea'] })

Vue.mixin({ methods: { t, n } })

export default new Vue({
	el: '#content',
	router,
	render: h => h(App),
})
