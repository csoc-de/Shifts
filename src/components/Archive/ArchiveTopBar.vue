<!--
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<!--
  - Component to display and navigate the Shifts-Calendar
  -->
<!--suppress JSUnfilteredForInLoop -->
<template>
	<div class="top-bar">
		<div class="buttons-bar">
			<div class="left">
				<NcButton
					color="light-blue"
					:disabled="archiveTimeRange === 12"
					@click="setTimeRange(12)">
					12 {{ t('shifts','Months') }}
				</NcButton>

				<NcButton
					color="light-blue"
					:disabled="archiveTimeRange === 9"
					@click="setTimeRange(9)">
					9 {{ t('shifts','Months') }}
				</NcButton>

				<NcButton
					color="light-blue"
					:disabled="archiveTimeRange === 6"
					@click="setTimeRange(6)">
					6 {{ t('shifts','Months') }}
				</NcButton>

				<NcButton
					color="light-blue"
					:disabled="archiveTimeRange === 3"
					@click="setTimeRange(3)">
					3 {{ t('shifts','Months') }}
				</NcButton>

				<NcButton
					color="light-blue"
					:disabled="archiveTimeRange === 1"
					@click="setTimeRange(1)">
					{{ t('shifts','Last Month') }}
				</NcButton>

				<div style="padding-left: 25px"></div>

				<NcDatetimePicker
					type="date"
					appendToBody
					v-model="startDate" />

				<div style="padding-left: 10px"></div>

				<NcDatetimePicker
					type="date"
					appendToBody
					v-model="endDate" />
			</div>
		</div>
	</div>
</template>

<script>
import dayjs from 'dayjs'
import store from '../../store'
import { mapGetters } from 'vuex'
import { NcButton, NcDatetimePicker } from '@nextcloud/vue'

export default {
	name: 'ArchiveTopBar',
	components: {
		NcButton,
		NcDatetimePicker,
	},
	data() {
		return {
			startDate: dayjs(),
			endDate: dayjs()
		}
	},
	methods: {
		// set time range for archive data
		setTimeRange(timeRange) {
			store.dispatch('setArchiveTimeRange', { timeRange })
		},
	},
	computed: {
		...mapGetters({
			archiveTimeRange: 'getArchiveTimeRange',
		}),
	},
	watch: {
		startDate: {
			handler(newValue) {
				if (this.endDate !== undefined) {
					store.dispatch('setArchiveTimeRange', { startDate: dayjs(newValue), endDate: dayjs(this.endDate) })
				}
			}
		},
		endDate: {
			handler(newValue) {
				if (this.startDate !== undefined) {
					store.dispatch('setArchiveTimeRange', { startDate: dayjs(this.startDate), endDate: dayjs(newValue) })
				}
			}
		}
	}
}
</script>
