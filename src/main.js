import 'core-js/stable'
import '@nextcloud/dialogs/styles/toast.scss'

import Vue from 'vue'
import App from './App'
import store from './store'
import router from './router'
import { sync } from 'vuex-router-sync'
import ClickOutside from 'vue-click-outside'
import VueClipboard from 'vue-clipboard2'
import VTooltip from 'v-tooltip'
import VueShortKey from 'vue-shortkey'
import { translate, translatePlural } from '@nextcloud/l10n'
import vuetify from './plugins/vuetify'

// to allow clicking autoside of popover
Vue.directive('ClickOutside', ClickOutside)

// adds v-popover Component to allow usage in App
Vue.component('v-popover', VTooltip.VPopover)

// changes appearence of VTooltip
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

sync(store, router)

// Translation Compatibility
Vue.prototype.$t = translate
Vue.prototype.$n = translatePlural

// Translation
Vue.prototype.t = translate
Vue.prototype.n = translatePlural

export default new Vue({
	el: '#content',
	router,
	vuetify,
	store,
	created() {
		this.$store.dispatch('setup')
		this.$store.dispatch('setupArchive')
		this.$store.dispatch('fetchSettings')
	},
	render: h => h(App),
})
