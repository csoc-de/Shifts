<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
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
			<span style="margin-left: 10px">Version: 1.9.0-dev</span>
		</template>
	</AppNavigationVue>
</template>

<script>
import { NcAppNavigation as AppNavigationVue, NcAppNavigationItem as AppNavigationItem } from '@nextcloud/vue'
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
