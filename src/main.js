/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 * @author Kevin Küchler <kevin.kuechler@csoc.de>
 */

import 'core-js/stable'

import Vue from 'vue'
import App from './App'
import store from './store'
import router from './router'
import { sync } from 'vuex-router-sync'
import ClickOutside from 'vue-click-outside'
import VueClipboard from 'vue-clipboard2'
import { VTooltip, VPopover } from 'v-tooltip'
import VueShortKey from 'vue-shortkey'
import { translate, translatePlural } from '@nextcloud/l10n'
import dayjs from 'dayjs'
import dayOfYear from 'dayjs/plugin/dayOfYear'
import weekOfYear from 'dayjs/plugin/weekOfYear'
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter'

// to allow clicking autoside of popover
Vue.directive('ClickOutside', ClickOutside)

// adds v-popover Component to allow usage in App
Vue.component('VPopover', VPopover)

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

dayjs.locale('de')
dayjs.extend(dayOfYear)
dayjs.extend(weekOfYear)
dayjs.extend(isSameOrAfter)

export default new Vue({
	el: '#content',
	router,
	store,
	created() {
		this.$store.dispatch('setup')
		this.$store.dispatch('fetchSettings')
	},
	render: h => h(App),
})
