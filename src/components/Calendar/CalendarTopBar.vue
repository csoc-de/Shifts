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
		<div class="left">
			<div class="buttons-bar">
				<div class="left">
					<NcButton
						@click="setToday">
						Heute
					</NcButton>
					<NcButton
						@click="prev">
						<template #icon>
							<ChevronLeft></ChevronLeft>
						</template>
					</NcButton>
					<NcButton
						@click="next">
						<template #icon>
							<ChevronRight></ChevronRight>
						</template>
					</NcButton>
				</div>
			</div>
		</div>

		<div class="spacer"></div>

		<div class="right">
			<NcMultiselect
				label="text"
				@change="updateCalendar"
				:options="calendarFormats"
				:value="calendarFormats.find((format) => format.value === selectedCalendarFormat)">
			</NcMultiselect>
		</div>
	</div>
</template>

<script>
import store from '../../store'
import { mapGetters } from 'vuex'

import { NcButton, NcMultiselect } from '@nextcloud/vue'
import ChevronLeft from 'vue-material-design-icons/ChevronLeft'
import ChevronRight from 'vue-material-design-icons/ChevronRight'

export default {
	name: 'CalendarTopBar',
	components: {
		NcButton,
		ChevronLeft,
		ChevronRight,
		NcMultiselect
	},
	data() {
		return {
			calendarFormats: [
				{
					value: 'week',
					text: t('shifts', 'Week'),
				},
				{
					value: 'month',
					text: t('shifts', 'Month'),
				},
			]
		}
	},
	methods: {
		// changes the time of calendar to current timespan including today
		setToday() {
			store.dispatch('setDisplayDateToday')
		},
		// move to previous timeinterval with given calendarformat
		prev() {
			store.dispatch('prevDisplayDate')
		},
		// move to next timeinterval with given calendarformat
		next() {
			store.dispatch('nextDisplayDate')
		},

		// changes the Calendar Timespan to Month or Week
		updateCalendar(format) {
			store.dispatch('setDisplayedDateFormat', format.value)
		},
	},
	computed: {
		...mapGetters({
			selectedCalendarFormat: 'currentDateDisplayFormat',
		}),
	},
}
</script>
