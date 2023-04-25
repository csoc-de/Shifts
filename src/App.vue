<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<!--
  - Main Component of Vue-App
  -->
<template>
	<div id="content" class="app-shifts">
		<AppNavigation />
		<AppContent>
			<div class="shifts_container">
				<div v-if="!loading">
					<router-view />
				</div>
				<div v-else>
					<h3>Loading...</h3>
				</div>
			</div>
		</AppContent>
	</div>
</template>

<script>
import { mapGetters } from 'vuex'
import AppNavigation from './components/Navigation/AppNavigation'
import { NcAppContent as AppContent } from '@nextcloud/vue'
export default {
	name: 'App',
	components: {
		AppContent,
		AppNavigation,
	},
	computed: {
		...mapGetters({
			loading: 'loading',
		}),
	},
}
</script>

<style lang="scss">
@import '../css/custom.scss';
</style>
