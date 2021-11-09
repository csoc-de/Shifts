<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -->

<!--
  - Component to display and navigate the Shifts-Calendar
  -->
<!--suppress JSUnfilteredForInLoop -->
<template>
	<div>
		<!--eslint-disable-->
		<v-row align="center">
			<v-col>
				<v-btn
					outlined
					class="mr-4"
					color="grey darken-2"
					@click="setToday">
					{{ $t('shifts', 'Heute') }}
				</v-btn>
				<v-btn
					fab
					text
					small
					color="grey darken-2"
					@click="prev">
					<v-icon small>
						icon-view-previous
					</v-icon>
				</v-btn>
				<v-btn
					fab
					text
					small
					color="grey darken-2"
					@click="next">
					<v-icon small>
						icon-view-next
					</v-icon>
				</v-btn>
			</v-col>
			<v-spacer></v-spacer>
			<v-col
				sm="2"
				class="padding_correction">
				<v-select
					class="calendar-format-select"
					:items="calendarFormats"
					outlined
					attach
					@change="updateCalendar">
				</v-select>
			</v-col>
		</v-row>
		<div style="margin-left: 10px;">
			{{ this.date.format('MMMM YYYY')}}
		</div>
		<div id="calendar-wrapper">
			<v-simple-table style="max-width:100%">
				<template v-slot:default>
					<thead>
					<tr>
						<th class="table-header__main" style="padding: 0 0; width: 8%;">
							{{ t('shifts','Analyst') }}
						</th>
						<th class="table-header__weekly" v-for="shiftType in weeklyShiftTypes" style="padding: 0 0; width: 5%;">
							{{ shiftType.name }}
						</th>
						<th class="table-header" v-for="day in timespanHeader" :style="cellStyle">
							{{ day.label }}
						</th>
					</tr>
					</thead>
					<tbody>
						<tr v-for="row in rows">
							<td>
								{{ row.label }}
							</td>
							<td v-for="shiftType in weeklyShiftTypes">
								<div class="drop-zone"
									@drop="onWeeklyDrop($event, shiftType, row)"
									@dragover.prevent
									@dragenter.prevent>
									<div v-for="shift in weeklyShift(shiftType, row)"
										 :key='shift.id'
										 :style="cellBackground(shiftType)"
										 :draggable="isAdmin"
										 @dragstart="startWeeklyDrag($event, shift)"
										 class="weekly-indicator">
									</div>
								</div>
							</td>
							<td v-for="day in timespanHeader">
								<div class="drop-zone"
									@drop="onDailyDrop($event, day, row)"
									@dragover.prevent
									@dragenter.prevent>
									<div v-for="shift in dailyShifts(day, row)"
										 :key='shift.id'
										 :style="cellBackground(shift.shiftsType)"
										 :draggable="isAdmin"
										 @dragstart="startDailyDrag($event, shift)"
										 class="weekly-indicator">
										{{ selectedCalendarFormat === 'week' ? shift.shiftsType.name : shift.shiftsType.name.substring(0, 2)}}
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</template>
			</v-simple-table>
		</div>
		<!-- eslint-enable-->
	</div>
</template>

<script>
import { mapGetters } from 'vuex'
import dayjs from 'dayjs'
export default {
	name: 'Calendar',
	data() {
		return {
			calendarFormats: [
				{
					value: 'month',
					text: t('shifts', 'Monat'),
				},
				{
					value: 'week',
					text: t('shifts', 'Woche'),
				},
			],
		}
	},
	computed: {
		...mapGetters({
			isAdmin: 'isAdmin',
			analysts: 'allAnalysts',
			shifts: 'displayedShifts',
			shiftsTypes: 'allShiftsTypes',
			date: 'currentDateDisplayed',
			selectedCalendarFormat: 'currentDateDisplayFormat',
		}),
		dateStart() {
			return dayjs().startOf('week').format('YYYY-MM-DD HH:mm')
		},
		dateEnd() {
			return dayjs().endOf('week').format('YYYY-MM-DD HH:mm')
		},
		rows() {
			const result = [
				{
					uid: '-1',
					label: t('shifts', 'Offene Schichten'),
				},
			]
			result.push(...this.analysts.map((analyst) => {
				return {
					uid: analyst.uid,
					label: analyst.name,
				}
			}))
			return result
		},
		weeklyShiftTypes() {
			return this.shiftsTypes.filter((element) => element.isWeekly === '1')
		},
		timespanHeader() {
			const date = this.date
			const start = date.startOf(this.selectedCalendarFormat)
			const end = date.endOf(this.selectedCalendarFormat)
			const format = this.selectedCalendarFormat === 'week' ? 'dddd DD.' : 'DD.'
			const dateFormat = 'YYYY-MM-DD'
			const result = []
			for (let i = 0; i <= end.diff(start, 'day'); i++) {
				const curr = start.add(i, 'day')
				result.push({
					label: curr.format(format),
					date: curr.format(dateFormat),
				})
			}
			return result
		},
		cellStyle() {
			let width = 5
			if (this.selectedCalendarFormat === 'week') {
				width = (92 - (this.weeklyShiftTypes.length * 5)) / 7
			} else if (this.selectedCalendarFormat === 'month') {
				width = (92 - (this.weeklyShiftTypes.length * 5)) / this.date.daysInMonth()
			}
			return {
				padding: '0 0',
				width: `${width}%`,
			}
		},
	},
	watch: {
		// watches or changes in the shifts Array to update the Calendar
		shifts: {
			handler(newVal, oldVal) {
				if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
					// TODO Shifts change
				}
			},
			deep: true,
		},
	},
	methods: {
		cellBackground(shiftsType) {
			return {
				'background-color': `${shiftsType.color}`,
			}
		},
		weeklyShift(weeklyShift, analyst) {
			const weeklyShifts = this.shifts.filter((shift) => {
				return shift.shiftTypeId === weeklyShift.id.toString()
			})
			return weeklyShifts.filter((shift) => {
				return shift.userId === analyst.uid
			})
		},
		dailyShifts(day, analyst) {
			const dayShifts = this.shifts.filter((shift) => {
				return day.date === shift.date
			})
			return dayShifts.filter((shift) => {
				return shift.userId === analyst.uid && shift.shiftsType.isWeekly === '0'
			})
		},
		onDailyDrop(event, day, analyst) {
			const shiftId = event.dataTransfer.getData('shiftId')
			const shiftTypeId = event.dataTransfer.getData('shiftTypeId')
			this.$store.dispatch('updateShift', {
				id: shiftId,
				analystId: analyst.uid,
				shiftTypeId,
				date: day.date,
			})
		},
		onWeeklyDrop(event, shiftType, analyst) {
			const shiftId = event.dataTransfer.getData('shiftId')
			this.$store.dispatch('updateShift', {
				id: shiftId,
				analystId: analyst.uid,
				shiftTypeId: shiftType.id,
				date: this.date.startOf('week').format('YYYY-MM-DD'),
			})
		},
		startDailyDrag(evt, shift) {
			evt.dataTransfer.dropEffect = 'move'
			evt.dataTransfer.effectAllowed = 'move'
			evt.dataTransfer.setData('shiftId', shift.id)
			evt.dataTransfer.setData('shiftTypeId', shift.shiftTypeId)
		},
		startWeeklyDrag(evt, shift) {
			evt.dataTransfer.dropEffect = 'move'
			evt.dataTransfer.effectAllowed = 'move'
			evt.dataTransfer.setData('shiftId', shift.id)
		},
		// changes the Calendar Timespan to Month or Week
		async updateCalendar(format) {
			this.dateChanged = true
			await this.$store.commit('updateDisplayedDateFormat', format)
		},
		// changes the time of calendar to current timespan including today
		async setToday() {
			const today = dayjs()
			await this.$store.commit('updateDisplayedDate', today)
		},
		// move to previous timeinterval with given calendarformat
		async prev() {
			let date = this.date
			date = date.add(-1, this.selectedCalendarFormat)
			await this.$store.commit('updateDisplayedDate', date)
		},
		// move to next timeinterval with given calendarformat
		async next() {
			let date = this.date
			date = date.add(1, this.selectedCalendarFormat)
			// updating the state of the calendar
			await this.$store.commit('updateDisplayedDate', date)
		},
	},
}
</script>

<style>

.toolbox {
	padding: 10px;
}
</style>
