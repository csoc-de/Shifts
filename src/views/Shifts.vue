<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -->

<!--
  - View to display Shifts and add Shifts or Shiftstypes if admin is current user
  -->
<template>
	<div class="shifts_content">
		<div v-if="isAdmin">
			<!--eslint-disable-->
			<v-menu	 v-if="!loading"
				v-model="shiftOpen"
				:close-on-content-click="false"
				:nudge-width="200"
				attach
				offset-y>
				<template v-slot:activator="{ on, attrs }">
					<v-btn
						color="light-blue"
						dark
						v-bind="attrs"
						v-on="on">
						{{ t('shifts','Add Shift') }}
					</v-btn>
				</template>
				<v-layout class="popover-menu-layout">
					<NewShift @close="closeNewShift()">
					</NewShift>
				</v-layout>
			</v-menu>
			<v-btn
				color="light-blue"
				:disabled="disabled || !settingsFetched"
				dark
				@click="syncCalendar">
				{{ t('shifts','Synchronize Calendar') }}
			</v-btn>
		</div>
		<Calendar v-if="!loading" />
		<!-- eslint-enable-->
	</div>
</template>
<script>
import Calendar from '../components/Calendar'
import NewShift from './NewShift'
import { mapGetters } from 'vuex'

export default {
	name: 'Shifts',
	components: {
		Calendar,
		NewShift,
	},
	data() {
		return {
			shiftOpen: false,
			disabled: false,
		}
	},
	computed: {
		...mapGetters({
			isAdmin: 'isAdmin',
			loading: 'loading',
			settingsFetched: 'getSettingsFetched',
		}),
	},
	methods: {
		closeNewShift() {
			this.shiftOpen = false
		},
		async syncCalendar() {
			this.disabled = true
			await this.$store.dispatch('syncCalendar')
			this.disabled = false
		},
	},
}
</script>
<style scoped>
#app-content > label{
	margin-left: 50px;
}

#app-content > div {
	width: 100%;
	height: 100%;
	margin-left: 50px;
	padding: 20px;
	display: flex;
	flex-direction: column;
	flex-grow: 1;
}

input[type='text'] {
	width: 100%;
}

textarea {
	flex-grow: 1;
	width: 100%;
}
</style>
