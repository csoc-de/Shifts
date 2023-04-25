<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<!--
  - View to display Shifts and add Shifts or Shiftstypes if admin is current user
  -->
<template>
	<div>
		<div
			v-if="isAdmin"
			class="top-bar">
			<div class="buttons-bar">
				<div class="left">
					<NcButton
						color="light-blue"
						:disabled="disabled || !settingsFetched"
						@click="openNewShift">
						{{ t('shifts','Add Shift') }}
					</NcButton>

					<NcButton
						color="light-blue"
						:disabled="disabled || !settingsFetched"
						@click="syncCalendar">
						{{ t('shifts','Synchronize Calendar') }}
					</NcButton>
				</div>
			</div>

			<NewShift
				v-if="shiftOpen"
				@close="closeNewShift()" />
		</div>

		<Calendar />
	</div>
</template>
<script>

import store from '../store'
import NewShift from './NewShift'
import { mapGetters } from 'vuex'
import { NcButton } from '@nextcloud/vue'
import { showError } from '@nextcloud/dialogs'
import Calendar from '../components/Calendar/Calendar'

export default {
	name: 'Shifts',
	components: {
		Calendar,
		NewShift,
		NcButton,
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
		openNewShift() {
			this.shiftOpen = true
		},
		closeNewShift() {
			this.shiftOpen = false
		},
		syncCalendar() {
			this.disabled = true
			store.dispatch('synchronizeCalendar').catch((e) => {
				showError(t('shifts', 'Failed to synchronize calendar'))
				console.error('Failed to synchronize calendar:', e)
			}).finally(() => {
				this.disabled = false
			})
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
