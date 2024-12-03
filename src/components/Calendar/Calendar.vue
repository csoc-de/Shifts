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
								analyst="open"
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
										@touchstart="handleTouchStart(openshift)"
										@touchmove="handleTouchMove($event, openshift)"
										@touchcancel="handleTouchStopped($event, openshift, day)"
										@touchend="handleTouchStopped($event, openshift, day)"
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
										@touchstart="handleTouchStart(openshift)"
										@touchmove="handleTouchMove($event, openshift)"
										@touchcancel="handleTouchStopped($event, openshift, day)"
										@touchend="handleTouchStopped($event, openshift, day)"
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
								:analyst="row.uid"
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
										@touchstart="handleTouchStart(shift)"
										@touchmove="handleTouchMove($event, shift)"
										@touchcancel="handleTouchStopped($event, shift, header, row.uid)"
										@touchend="handleTouchStopped($event, shift, header, row.uid)"
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
										@touchstart="handleTouchStart(shift)"
										@touchmove="handleTouchMove($event, shift)"
										@touchcancel="handleTouchStopped($event, shift, header, row.uid)"
										@touchend="handleTouchStopped($event, shift, header, row.uid)"
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

			// Mobile attributes
			touchStartDragShiftId: -1,
			touchEndDragShiftId: -1,
			touchShiftId: -1,
			currentAnalystId: -1,
			updatedAnalystId: -1,
			touchMovingElement: undefined,
			indicatorStartPosition: {
				left: 0,
				top: 0,
			}
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
			this.removeIndicatorAndResetMovingInfo();
		},

		onDragHoverRow(event, row) {
			this.hoveringRow = row
		},
		cancelDrag(event) {
			this.hoveringRow = undefined
		},
		startDailyDrag(event, shift, day) {
			if(!event.dataTransfer) {
				event.dataTransfer = new DataTransfer();
			}

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
			if(!event.dataTransfer) {
				event.dataTransfer = new DataTransfer();
			}

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
		},

		handleTouchStart(startShiftData) {
			if(this.touchStartDragShiftId != startShiftData.id) {
				this.touchStartDragShiftId = startShiftData.id;
			}
		},

		handleTouchMove(event, moveShiftData) {
			if(this.touchEndDragShiftId === moveShiftData.id) {
				this.touchMovingElement.style.left = (event.touches[0].clientX - 50) + 'px';
				this.touchMovingElement.style.top = (event.touches[0].clientY - 50) + 'px';
				this.updatedAnalystId = this.findAnalystUid(event.touches[0]);
			} else {
				this.touchStartDragShiftId = -1;
				this.touchEndDragShiftId = -1;
			}
		},

		handleTouchStopped(event, stoppedShiftData, date, currentAnalystUid) {
			if(this.isDeletionTouched(event.target)) {
				// Delete clicked, so do nothing
				return;
			}

			if(this.touchEndDragShiftId === -1 && this.touchStartDragShiftId === stoppedShiftData.id) {
				this.touchEndDragShiftId = stoppedShiftData.id;
				this.currentAnalystId = currentAnalystUid;
				if (!this.updatedAnalystId || this.updatedAnalystId === -1) {
					this.updatedAnalystId = currentAnalystUid;
				}
				const shiftContainer = this.findShiftContainerElement(event.target);
				const deleteButtons = shiftContainer.getElementsByTagName("BUTTON");
				if(deleteButtons.length > 0) {
					deleteButtons[0].classList.add("displayNone");
				}
				const indicator = shiftContainer.cloneNode(true);
				indicator.classList.add("ableToMoveIndicator");
				const shiftContainerBoundingRect = shiftContainer.getBoundingClientRect();
				this.indicatorStartPosition.left = shiftContainerBoundingRect.x;
				this.indicatorStartPosition.top = shiftContainerBoundingRect.y - shiftContainerBoundingRect.height;
				this.touchMovingElement = indicator;
				shiftContainer.parentNode.insertBefore(
					indicator,
					shiftContainer
				);
				this.setIndicatorToStartPosition();
			} else if(this.touchEndDragShiftId !== -1 && this.touchEndDragShiftId === this.touchStartDragShiftId){
				if(this.currentAnalystId != this.updatedAnalystId) {
					const mockEvent = {};
					const isWeekly = stoppedShiftData.isWeekly || date.isWeekly;
					let analyst = this.shiftRows.find(analyst => analyst.uid === this.updatedAnalystId);
					if(analyst) { 
						if(isWeekly) {
							this.startWeeklyDrag(mockEvent, stoppedShiftData, date);
							this.onWeeklyDrop(mockEvent, date, analyst);
						} else {
							this.startDailyDrag(mockEvent, stoppedShiftData, date);
							this.onDailyDrop(mockEvent, date, analyst);
						}
						this.removeIndicatorAndResetMovingInfo();
					} else {
						this.updatedAnalystId = this.currentAnalystId;
						this.setIndicatorToStartPosition();
					}
				} else {
					// user did move it to the same spot or and invalid , so remove indicator and reset moving info
					this.removeIndicatorAndResetMovingInfo();
				}
			}
		},

		findAnalystUid(touchElement) {
			let tdElement = document.elementFromPoint(touchElement.clientX, touchElement.clientY);
			let tries = 0;
			
			while (tdElement != null && tdElement.tagName != "TD" && tries < 5) {
				tdElement = tdElement.parentNode;
				tries++;
			}
			if(tries === 5 || tdElement === null) {
				// No analyst found so reset
				this.setIndicatorToStartPosition();
				return undefined;
			}
			return tdElement.getAttribute("analyst");
		},

		findShiftContainerElement(element) {
			let currentElement = element;
			let tries = 0;
			
			while (!currentElement.classList.contains('shiftContainer') && tries < 5) {
				currentElement = currentElement.parentNode;
				tries++;
			}
			if(!currentElement.classList.contains('shiftContainer')) {
				currentElement = element;
				tries = 0;
				while (!currentElement.classList.contains('shiftContainer') && tries < 5) {
					currentElement = currentElement.firstChild;
					tries++;
				}
				if(!currentElement.classList.contains('shiftContainer')) {
					throw new Error("Didn't find ShiftContainerElement. Giving Up.");
				}
			}
			return currentElement;
		},

		isDeletionTouched(touchElement) {
			let buttonElement = touchElement;
			let tries = 0;
			while (buttonElement.tagName != "BUTTON" && tries < 8) {
				buttonElement = buttonElement.parentNode;
				tries++;
			}
			return tries !== 8;
		},

		setIndicatorToStartPosition() {
			const indicator = document.getElementsByClassName("ableToMoveIndicator")[0];
			indicator.style.left = this.indicatorStartPosition.left + 'px';
			indicator.style.top = this.indicatorStartPosition.top + 'px';
		},

		removeIndicatorAndResetMovingInfo() {
			this.currentAnalystId = -1;
			this.updatedAnalystId = -1;
			this.touchEndDragShiftId = -1;
			this.touchShiftId = -1;
			this.touchStartDragShiftId = -1;
			this.touchMovingElement = undefined;
			[...document.getElementsByClassName("displayNone")].map(n => n && n.classList.remove("displayNone"));
			[...document.getElementsByClassName("ableToMoveIndicator")].map(n => n && n.remove());
		},
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
		overflow: scroll;

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

.ableToMoveIndicator {
	position: absolute;
	opacity: 0.5;
	color: red;
	pointer-events: none;
}

.displayNone {
	display: none;
}

</style>
