<!--
  - App Navigation Sidebar Component
  -->
<template>
	<AppNavigationVue>
		<template #list>
			<AppNavigationItem
				:title="t('shifts', 'Schichtplan')"
				icon="icon-calendar-dark"
				to="/" />

			<AppNavigationItem
				:title="t('shifts', 'Schichttausch')"
				icon="icon-category-organization"
				to="/requests" />

			<AppNavigationItem
				v-if="isAdmin && !loading"
				:title="t('shifts', 'Schichttypen')"
				icon="icon-category-customization"
				to="/shiftsTypes" />
		</template>
		<template #footer>
			Version: 1.5.0r
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
