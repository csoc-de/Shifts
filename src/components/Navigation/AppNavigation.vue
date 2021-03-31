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
				:title="t('shifts', 'Schichtwechsel')"
				icon="icon-category-organization"
				to="/requests" />
		</template>
	</AppNavigationVue>
</template>

<script>
import { AppNavigation as AppNavigationVue, AppNavigationItem } from '@nextcloud/vue'
export default {
	name: 'AppNavigation',
	components: {
		AppNavigationVue,
		AppNavigationItem,
	},
}
</script>
