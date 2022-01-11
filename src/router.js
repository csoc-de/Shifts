/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

import Vue from 'vue'
import Router from 'vue-router'
import { getRootUrl, generateUrl } from '@nextcloud/router'

import Shifts from './views/Shifts'
import Requests from './views/Requests'
import ShiftsTypes from './views/ShiftsTypes'
import Archive from './views/Archive'

import store from './store'

Vue.use(Router)

const webRootWithIndexPHP = getRootUrl() + '/index.php'
const doesURLContainIndexPHP = window.location.pathname.startsWith(webRootWithIndexPHP)
const base = generateUrl('apps/shifts', {}, {
	noRewrite: doesURLContainIndexPHP,
})

// Vue Router
const router = new Router({
	mode: 'history',
	base,
	routes: [
		{
			path: '/',
			redirect: '/timeline',
		},
		{
			path: '/timeline',
			component: Shifts,
			name: 'MainView',
		},
		{
			path: '/requests',
			component: Requests,
			name: 'RequestsView',
		},
		{
			path: '/shiftsTypes',
			component: ShiftsTypes,
			name: 'ShiftsTypes',
			beforeEnter: async (to, from, next) => {
				const val = await store.dispatch('requestAdminStatus')
				if (val) {
					next()
				} else {
					next('/timeline')
				}
			}
		},
		{
			path: '/archive',
			component: Archive,
			name: 'Archive',
			beforeEnter: async (to, from, next) => {
				const val = await store.dispatch('requestAdminStatus')
				if (val) {
					next()
				} else {
					next('/timeline')
				}
			}
		},
	],
})

export default router
