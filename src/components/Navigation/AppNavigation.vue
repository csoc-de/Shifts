<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -->

<!--
  - App Navigation Sidebar Component
  -->
<template>
	<AppNavigationVue>
		<template #list>
			<AppNavigationItem
				:title="t('shifts', 'Shift plan')"
				icon="icon-calendar-dark"
				to="/timeline" />

			<AppNavigationItem
				:title="t('shifts', 'Shiftchange')"
				icon="icon-category-organization"
				to="/requests" />

			<AppNavigationItem
				v-if="isAdmin && !loading"
				:title="t('shifts', 'Shifttypes')"
				icon="icon-category-customization"
				to="/shiftsTypes" />

			<AppNavigationItem
				v-if="isAdmin && !loading"
				:title="t('shifts', 'Shiftsdata')"
				icon="icon-category-files"
				to="/archive" />
		</template>
		<template #footer>
			Version: 1.8.5
		</template>
	</AppNavigationVue>
</template>

<script>
import { AppNavigation as AppNavigationVue, AppNavigationItem } from '@nextcloud/vue'
import { mapGetters } from 'vuex'
export default {
	name: 'AppNavigation',
	components: {
		AppNavigationVue,
		AppNavigationItem,
	},
	computed: {
		...mapGetters({
			isAdmin: 'isAdmin',
			loading: 'loading',
		}),
	},
}
</script>
