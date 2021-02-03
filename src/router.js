import Vue from 'vue'
import Router from 'vue-router'
import { getRootUrl, generateUrl } from '@nextcloud/router'

import Main from './views/Main'

import NewShift from './views/NewShift'
import NewShiftType from './views/NewShiftType'

Vue.use(Router)

const webRootWithIndexPHP = getRootUrl() + '/index.php'
const doesURLContainIndexPHP = window.location.pathname.startsWith(webRootWithIndexPHP)
const base = generateUrl('apps/shifts', {}, {
	noRewrite: doesURLContainIndexPHP,
})

const router = new Router({
	mode: 'history',
	base,
	routes: [
		{
			path: '/',
			component: Main,
			name: 'MainView',
			children: [
				{
					path: '/newShift',
					name: 'NewShiftPopoverView',
					component: NewShift,
				},
				{
					path: '/newShiftType',
					name: 'NewShiftTypePopoverView',
					component: NewShiftType,
				},
			],
		},
	],
})

export default router
