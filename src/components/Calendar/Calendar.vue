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
	<div>
		<CalendarTopBar></CalendarTopBar>

		<div class="content">
			<div class="header">
				{{ this.date.format('MMMM YYYY') }}{{ (kw !== undefined ? ' - KW ' + kw : '') }}
			</div>

			<!-- Calendar -->
			<div class="calendar">
				<table>
					<thead>
						<!-- Table header for date -->
						<tr>
							<th>
								{{ t('shifts','Analyst') }}
							</th>

							<th
								v-for="(header, i) in timespanHeaders"
								:key="i"
								:class="isCurrentDay(header) ? 'today' : ''">
								<span v-if="header.isWeekly">
									{{t('shifts', 'Weekly Shifts')}}
								</span>
								<br>
								{{ header.label }}
							</th>
						</tr>

						<!-- Table header for open shifts -->
						<tr>
							<th>
								{{ t('shifts', 'Open Shifts') }}
							</th>

							<td
								v-for="(day, i) in timespanHeaders"
								:key="i"
								:class="isCurrentDay(day) ? 'today' : ''">
								<div
									v-if="day.isWeekly"
									class="container">
									<div v-for="(openshift, j) in getOpenWeeklyShifts(day.week)"
										:key="j"
										:style="cellBackground(openshift)"
										:draggable="isAdmin"
										@dragend="cancelDrag"
										@dragstart="startWeeklyDrag($event, openshift, day)"
										class="row shiftContainer pad">
										<div
											class="col"
											style="display:flex; align-items: center">
											<span>{{openshift.name}}</span>
										</div>
									</div>
								</div>
								<div
									v-else
									class="container">
									<div v-for="(openshift, j) in getOpenDailyShifts(day.date)"
										:key="j"
										:style="cellBackground(openshift)"
										:draggable="isAdmin"
										@dragend="cancelDrag"
										@dragstart="startDailyDrag($event, openshift, day)"
										class="row shiftContainer pad">
										<div
											class="col"
											style="display:flex; align-items: center">
											<span>{{openshift.name}}</span>
										</div>
										<!-- Delete unassigned shift? <div
											class="col">
											<NcButton
												v-if="isAdmin"
												@click="deleteShift(openshift)"
												type="error">
												<template #icon>
													<Delete :size="16" />
												</template>
											</NcButton>
										</div> -->
									</div>
								</div>
							</td>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(row, i) in shiftRows" :key="i" :class="hoveringRow === i ? 'marked' : ''">
							<td>
								{{ row.label }}
							</td>

							<!-- For every header create a table data cell (td) -->
							<td
								v-for="(header, j) in timespanHeaders"
								:key="j"
								:class="isCurrentDay(header) ? 'today' : ''">
								<div v-if="header.isWeekly"
									class="drop-zone container"
									@drop="onWeeklyDrop($event, header, row)"
									@dragenter="onDragHoverRow($event, i)"
									@dragover.prevent
									@dragenter.prevent>
									<div v-for="(shift, i) in getWeeklyShiftsForAnalyst(row.uid, header.week)"
										:key='i'
										:draggable="isAdmin"
										:style="cellBackground(shift.shiftsType)"
										@dragend="cancelDrag"
										@dragstart="startWeeklyDrag($event, shift, header)"
										class="row shiftContainer pad">
										<div
											class="col"
											style="display:flex; align-items: center">
											<span>{{shift.shiftsType.name}}</span>
										</div>
										<div
											class="col"
											style="padding: 0">
											<NcButton
												v-if="isAdmin"
												@click="deleteShift(shift)"
												type="error">
												<template #icon>
													<Delete :size="16" />
												</template>
											</NcButton>
										</div>
									</div>
								</div>
								<div v-else
									class="drop-zone container"
									@drop="onDailyDrop($event, header, row)"
									@dragenter="onDragHoverRow($event, i)"
									@dragover.prevent
									@dragenter.prevent>
									<div v-for="(shift, i) in getDailyShiftsForAnalyst(row.uid, header.date)"
										:key='i'
										:draggable="isAdmin"
										:style="cellBackground(shift.shiftsType)"
										@dragend="cancelDrag"
										@dragstart="startDailyDrag($event, shift, header)"
										class="row shiftContainer pad">
										<div
											class="col"
											style="display:flex; align-items: center">
											<span>{{shift.shiftsType.name}}</span>
										</div>
										<div
											class="col"
											style="padding: 0">
											<NcButton
												v-if="isAdmin"
												@click="deleteShift(shift)"
												type="error">
												<template #icon>
													<Delete :size="16" />
												</template>
											</NcButton>
										</div>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<!-- eslint-enable-->
	</div>
</template>

<script>
import dayjs from 'dayjs'
import { mapGetters } from 'vuex'
import { NcButton } from '@nextcloud/vue'
import CalendarTopBar from './CalendarTopBar'
import Delete from 'vue-material-design-icons/Delete'
import store from '../../store'
import { showError } from '@nextcloud/dialogs'
export default {
	name: 'Calendar',
	components: {
		Delete,
		NcButton,
		CalendarTopBar,
	},
	data() {
		return {
			calendarFormats: [
				{
					value: 'month',
					text: t('shifts', 'Month'),
				},
				{
					value: 'week',
					text: t('shifts', 'Week'),
				},
			],
			timespanHeaders: [],
			shiftRows: [],

			kw: undefined,
			today: dayjs(),

			hoveringRow: undefined,
		}
	},
	methods: {
		isCurrentDay(header) {
			return this.today.isSame(header.date, 'day') && !header.isWeekly
		},
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
				return shift.userId === analyst.uid && (shift.shiftsType.isWeekly === '0' || !shift.shiftsType.isWeekly)
			})
		},
		deleteShift(shift) {
			if (shift.userId !== '-1') {
				shift.analystId = '-1'
				shift.oldAnalystId = shift.userId
				shift.saveChanges = true
				store.dispatch('updateShift', shift).then(async () => {
					await store.dispatch('fetchAllShifts')
					await store.dispatch('fetchCurrentShifts')
				}).catch((e) => {
					showError(t('shifts', 'Could not fetch shifts'))
				})
			} else {
				this.$store.dispatch('deleteShift', shift)
			}
		},

		onDragHoverRow(event, row) {
			this.hoveringRow = row
		},
		cancelDrag(event) {
			this.hoveringRow = undefined
		},
		startDailyDrag(event, shift, day) {
			event.dataTransfer.dropEffect = 'move'
			event.dataTransfer.effectAllowed = 'move'

			// Is object shift type e.g. unassigned shift?
			if (shift.isWeekly !== undefined && shift.shiftsType === undefined) {
				event.dataTransfer.setData('isUnassigned', true)
				event.dataTransfer.setData('shiftTypeId', shift.id)
			} else {
				event.dataTransfer.setData('isUnassigned', false)
				event.dataTransfer.setData('shiftId', shift.id)
				event.dataTransfer.setData('shiftTypeId', shift.shiftTypeId)
				event.dataTransfer.setData('oldAnalyst', shift.userId)
			}
			event.dataTransfer.setData('date', day.date)
		},
		onDailyDrop(event, day, analyst) {
			this.cancelDrag()

			const date = event.dataTransfer.getData('date')
			const shiftTypeId = parseInt(event.dataTransfer.getData('shiftTypeId'))
			const isUnassigned = event.dataTransfer.getData('isUnassigned') === 'true'

			// Validation
			if (day.isWeekly) {
				console.error('You cannot set a weekly shift as a daily shift!')
				showError(t('shifts', 'You cannot set a weekly shift as a daily shift!'))
				return
			} else if (date !== day.date) {
				console.error('You cannot set a daily shift to another day. Stay in the same day please:', date)
				return
			} else if (this.hasAnalystDailyShift(analyst.uid, day.date, shiftTypeId)) {
				console.error('You cannot assign a shift twice to an user!')
				showError(t('shifts', 'You cannot assign a shift twice to an user!'))
				return
			} else if (!this.hasAnalystMinShiftLevel(analyst.uid, shiftTypeId)) {
				console.error('User has an insufficient skill level!')
				showError(t('shifts', 'User has an insufficient skill level!'))
				return
			}

			if (isUnassigned) {
				store.dispatch('createNewShift', {
					// $analystId, $shiftTypeId, $date
					analystId: analyst.uid,
					shiftTypeId,
					date: day.date
				}).then(async () => {
					await store.dispatch('fetchAllShifts')
					await store.dispatch('fetchCurrentShifts')
				}).catch((e) => {
					console.error('Failed to assign shift:', e)
					if (e.status) {
						if (e.status === 401) {
							showError(t('shifts', 'You are NOT allowed to create or change shifts!'))
						} else {
							showError(t('shifts', 'Failed to assign shift!'))
						}
					} else {
						showError(t('shifts', 'Failed to assign shift!'))
					}
				})
			} else {
				const shiftId = event.dataTransfer.getData('shiftId')
				const oldAnalystId = event.dataTransfer.getData('oldAnalyst')

				if (oldAnalystId !== analyst.uid) {
					store.dispatch('updateShift', {
						id: shiftId,
						analystId: analyst.uid,
						shiftTypeId,
						date: day.date,
						oldAnalystId,
						saveChanges: true,
					}).then(async () => {
						await store.dispatch('fetchAllShifts')
						await store.dispatch('fetchCurrentShifts')
					})
				}
			}
		},

		startWeeklyDrag(event, shift, week) {
			event.dataTransfer.dropEffect = 'move'
			event.dataTransfer.effectAllowed = 'move'

			// Is object shift type e.g. unassigned shift?
			if (shift.isWeekly !== undefined && shift.shiftsType === undefined) {
				event.dataTransfer.setData('isUnassigned', true)
				event.dataTransfer.setData('shiftTypeId', shift.id)
			} else {
				event.dataTransfer.setData('isUnassigned', false)
				event.dataTransfer.setData('shiftId', shift.id)
				event.dataTransfer.setData('shiftTypeId', shift.shiftTypeId)
				event.dataTransfer.setData('oldAnalyst', shift.userId)
			}
			event.dataTransfer.setData('weekNo', week.week)
		},
		onWeeklyDrop(event, week, analyst) {
			this.cancelDrag()

			const shiftTypeId = parseInt(event.dataTransfer.getData('shiftTypeId'))
			const weekNo = parseInt(event.dataTransfer.getData('weekNo'))
			const isUnassigned = event.dataTransfer.getData('isUnassigned') === 'true'

			// Validation
			if (!week.isWeekly) {
				console.error('You cannot set a daily shift as a weekly shift!')
				showError(t('shifts', 'You cannot set a daily shift as a weekly shift!'))
				return
			} else if (weekNo !== week.week) {
				console.error('You cannot set a weekly shift to another week. Stay in the same week number please:', weekNo, '!=', week.week)
				showError(t('shifts', 'You cannot set a weekly shift to another week. Stay in the same week number please.'))
				return
			} else if (this.hasAnalystWeeklyShift(analyst.uid, weekNo, shiftTypeId)) {
				console.error('You cannot assign a shift twice to an user!')
				showError(t('shifts', 'You cannot assign a shift twice to an user!'))
				return
			} else if (!this.hasAnalystMinShiftLevel(analyst.uid, shiftTypeId)) {
				console.error('User has an insufficient skill level!')
				showError(t('shifts', 'User has an insufficient skill level!'))
				return
			}

			if (isUnassigned) {
				store.dispatch('createNewShift', {
					// $analystId, $shiftTypeId, $date
					analystId: analyst.uid,
					shiftTypeId,
					date: week.date
				}).then(async () => {
					await store.dispatch('fetchAllShifts')
					await store.dispatch('fetchCurrentShifts')
				})
			} else {
				const shiftId = event.dataTransfer.getData('shiftId')
				const oldAnalystId = event.dataTransfer.getData('oldAnalyst')

				if (oldAnalystId !== analyst.uid) {
					store.dispatch('updateShift', {
						id: shiftId,
						analystId: analyst.uid,
						shiftTypeId,
						date: week.date,
						oldAnalystId,
						saveChanges: true,
					}).then(async () => {
						await store.dispatch('fetchAllShifts')
						await store.dispatch('fetchCurrentShifts')
					})
				}
			}
		},

		updateCalendar() {
			this.updateHeaderValues()
			this.updateAnalysts()
		},
		updateHeaderValues() {
			let format = null
			switch (this.selectedCalendarFormat) {
			case 'month':
				format = 'DD.'
				break
			case 'week':
			default:
				format = 'dddd DD.'
				break
			}
			this.timespanHeaders = []

			const start = this.date.startOf(this.selectedCalendarFormat)
			const end = this.date.endOf(this.selectedCalendarFormat)
			for (let i = 0; i <= end.diff(start, 'day'); i++) {
				const curr = start.add(i, 'day')
				if (curr.day() === 1 || this.timespanHeaders.length === 0) {
					this.timespanHeaders.push({
						isWeekly: true,
						week: curr.week(),
						label: 'KW ' + curr.week(),
						date: curr.format('YYYY-MM-DD'),
					})
				}
				this.timespanHeaders.push({
					isWeekly: false,
					label: curr.format(format),
					date: curr.format('YYYY-MM-DD'),
				})
			}
		},
		updateAnalysts() {
			this.shiftRows = []
			for (const i in this.analysts) {
				this.shiftRows.push({
					uid: this.analysts[i].uid,
					label: this.analysts[i].name
				})
			}
		}
	},
	computed: {
		...mapGetters({
			isAdmin: 'isAdmin',
			analysts: 'allAnalysts',
			shifts: 'displayedShifts',
			shiftsTypes: 'allShiftsTypes',
			date: 'currentDateDisplayed',
			dailyShiftTypes: 'getDailyShiftTypes',
			selectedCalendarFormat: 'currentDateDisplayFormat',
			getOpenDailyShifts: 'getOpenDailyShifts',
			getOpenWeeklyShifts: 'getOpenWeeklyShifts',
			getDailyShiftsForAnalyst: 'getDailyShiftsForAnalyst',
			getWeeklyShiftsForAnalyst: 'getWeeklyShiftsForAnalyst',
			hasAnalystDailyShift: 'hasAnalystDailyShift',
			hasAnalystWeeklyShift: 'hasAnalystWeeklyShift',
			hasAnalystMinShiftLevel: 'hasAnalystMinShiftLevel',
		}),

		weeklyShiftTypes() {
			return this.shiftsTypes.filter((element) => element.isWeekly === '1')
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
		date: {
			handler(newValue) {
				this.kw = newValue.week()
				this.updateCalendar()
			}
		},
		selectedCalendarFormat: {
			handler(newValue) {
				this.updateCalendar()
			}
		}
	},
	created() {
		this.kw = this.date.week()
		this.updateCalendar()
	}
}
</script>

<style lang="scss" scoped>
.toolbox {
	padding: 10px;
}

.pad {
	margin: 2px !important;
}

.content {
	.calendar {
		max-width: 100%;

		table {
			line-height: 1.5;
			max-width: 100%;
			border-spacing: 0;
			border-color: grey;
			border-collapse: collapse;
			border: 1px solid;

			thead {
				tr {
					th {
						height: 52px;
						padding: 0 16px;
						font-weight: bold;
						border: 1px solid;
					}
					th:nth-child(2) {
						margin-right: 4px;
					}

					td {
						height: 52px;
						padding: 0 16px;
						border: 1px solid;
					}
				}
			}

			tbody {
				tr {
					border: 1px solid;
					border-bottom: 1px;

					td {
						height: 52px;
						padding: 4px 16px;
						border: 1px solid;
					}
				}
				tr:hover {
					background-color: transparent;
				}
				tr:nth-child(2n) {
					background-color: var(--color-background-dark);
				}
				tr.marked {
					background-color: var(--color-background-darker);
				}
			}
		}
	}
}

.shiftContainer {
	height: 52px;
	border-radius: 14px;
	color: black;
	margin: 2px 0;
}

.today {
	background-color: var(--color-text-maxcontrast);
}

</style>
