<template>
	<VueContent id="content" app-name="shifts">
		<AppNavigation />
		<AppContent>
			<v-app>
				<router-view />
			</v-app>
		</AppContent>
	</VueContent>
</template>

<script>
import AppNavigation from './components/Navigation/AppNavigation'
import VueContent from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
export default {
	name: 'App',
	components: {
		VueContent,
		AppContent,
		AppNavigation,
	},
}
</script>
