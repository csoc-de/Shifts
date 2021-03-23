import 'core-js/stable'
import '@nextcloud/dialogs/styles/toast.scss'
import 'material-design-icons-iconfont/dist/material-design-icons.css'

import Vue from 'vue'
import App from './App'
import router from './router'
import ClickOutside from 'vue-click-outside'
import VueClipboard from 'vue-clipboard2'
import VTooltip from 'v-tooltip'
import VueShortKey from 'vue-shortkey'
import { translate, translatePlural } from '@nextcloud/l10n'
import vuetify from './plugins/vuetify'

Vue.directive('ClickOutside', ClickOutside)
Vue.component('v-popover', VTooltip.VPopover)
Vue.use(VTooltip, {
	popover: {
		defaultWrapperClass: 'popover__wrapper',
		defaultBaseClass: 'event-popover popover',
		defaultInnerClass: 'popover__inner',
		defaultArrowClass: 'popover__arrow',
	},
})
Vue.use(VueClipboard)
Vue.use(VueShortKey, { prevent: ['input', 'textarea'] })

Vue.mixin({ methods: { t, n } })

Vue.prototype.$t = translate
Vue.prototype.$n = translatePlural

Vue.prototype.t = translate
Vue.prototype.n = translatePlural

export default new Vue({
	el: '#content',
	router,
	vuetify,
	render: h => h(App),
})
