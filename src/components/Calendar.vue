<!--
  - Component to display and navigate the Shifts-Calendar
  - Implements the GSTC-Calendar by neuronetio
  - https://github.com/neuronetio/gantt-schedule-timeline-calendar
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
		<!-- eslint-enable-->
		<div class="gstc-wrapper">
			<div ref="gstc" />
		</div>
	</div>
</template>

<script>
import GSTC from 'gantt-schedule-timeline-calendar'
import { Plugin as TimeLinePointer } from 'gantt-schedule-timeline-calendar/dist/plugins/timeline-pointer.esm.min.js'
import { Plugin as MovementPlugin } from 'gantt-schedule-timeline-calendar/dist/plugins/item-movement.esm.min.js'
import { Plugin as Selection } from 'gantt-schedule-timeline-calendar/dist/plugins/selection.esm.min.js'
import 'gantt-schedule-timeline-calendar/dist/style.css'
import { translate } from '@nextcloud/l10n'
import { mapGetters } from 'vuex'
import dayOfYear from 'dayjs/plugin/dayOfYear'
import dayjs from 'dayjs'
import 'dayjs/locale/de'
import { showWarning } from '@nextcloud/dialogs'

let gstc, state

function getMonthTranslated() {
	return translate('shifts', 'Monat')
}
function getWeekTranslated() {
	return translate('shifts', 'Woche')
}

export default {
	name: 'Calendar',
	data() {
		return {
			typeToLabel: {
				month: getMonthTranslated(),
				week: getWeekTranslated(),
			},
			calendarFormats: [
				{
					value: 'month',
					text: getMonthTranslated(),
				},
				{
					value: 'week',
					text: getWeekTranslated(),
				},
			],
			dateChanged: true,
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
			gstcLicense: 'getGstcLicense',
		}),
	},
	watch: {
		// watches or changes in the shifts Array to update the Calendar
		shifts: {
			handler(newVal, oldVal) {
				if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
					state.update('config', config => {
						config.chart.items = GSTC.api.fromArray(this.generateItems())
						config.list.rows = GSTC.api.fromArray(this.generateRows())
						return config
					})
				}
			},
			deep: true,
		},
	},
	mounted() {
		dayjs.extend(dayOfYear)
		dayjs.locale('de')
		const today = dayjs()
		const plugins = [TimeLinePointer()]
		const store = this.$store
		const movementPluginConfig = {
			events: {
				onMove({ items }) {
					return items.before.map((beforeMovementItem, index) => {
						const afterMovementItem = items.after[index]
						const myItem = GSTC.api.merge({}, afterMovementItem)
						if (myItem.time.start !== beforeMovementItem.time.start && myItem.time.end !== beforeMovementItem.time.end) {
							myItem.time = { ...beforeMovementItem.time }
							myItem.rowId = beforeMovementItem.rowId
						}
						return myItem
					})
				},
				onEnd({ items }) {
					return items.initial.map((initialItem, index) => {
						const afterItem = items.after[index]
						const shiftsId = GSTC.api.sourceID(initialItem.id)
						let newAnalystId = GSTC.api.sourceID(afterItem.rowId)

						newAnalystId = newAnalystId.replaceAll('_', '.')

						const newDate = GSTC.api.date(afterItem.time.start).format('YYYY-MM-DD')

						const oldShift = store.getters.getShiftById(shiftsId)
						const newShift = {
							id: shiftsId,
							analystId: newAnalystId,
							shiftTypeId: oldShift.shiftTypeId,
							date: newDate,
						}

						const analyst = store.getters.getAnalystById(newAnalystId)
						const shiftsType = store.getters.getShiftsTypeById(oldShift.shiftTypeId)
						if (analyst && analyst.skillGroup < shiftsType.skillGroupId) {
							showWarning(t('shifts', 'Dieser Analyst entspricht nicht den Anforderungen'))
						}
						store.dispatch('updateShift', newShift)
						return afterItem
					})
				},
			},
		}
		if (this.isAdmin) {
			plugins.push(Selection())
			plugins.push(MovementPlugin(movementPluginConfig))
		}
		// config for the GSTC Calendar
		const data = {}
		this.shiftsTypes.forEach((shiftsType) => {
			if (shiftsType.isWeekly === '1') {
				data[GSTC.api.GSTCID(shiftsType.name)] = {
					id: GSTC.api.GSTCID(shiftsType.name),
					width: 100,
					data: shiftsType.name,
					header: {
						content: t('shifts', shiftsType.name),
					},
				}
			}
		})
		data[[GSTC.api.GSTCID('id')]] = {
			id: GSTC.api.GSTCID('id'),
			width: 100,
			data: 'label',
			header: {
				content: 'Analyst',
			},
		}
		const config = {
			licenseKey: this.gstcLicense,
			plugins,
			innerHeight: 600,
			list: {
				columns: {
					data,
				},
				toggle: {
					display: false,
				},
				rows: GSTC.api.fromArray(this.generateRows()),
			},
			chart: {
				items: GSTC.api.fromArray(this.generateItems()),
				time: {
					from: today.startOf('week').valueOf(),
					to: today.endOf('week').valueOf(),
					calculatedZoomMode: true,
				},
			},
			locale: {
				name: 'de',
				weekdays: 'Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag'.split('_'),
				weekdaysShort: 'So._Mo._Di._Mi._Do._Fr._Sa.'.split('_'),
				weekdaysMin: 'So_Mo_Di_Mi_Do_Fr_Sa'.split('_'),
				months: 'Januar_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember'.split('_'),
				monthsShort: 'Jan_Feb_März_Apr_Mai_Juni_Juli_Aug_Sept_Okt_Nov_Dez'.split('_'),
				weekStart: 1,
				yearStart: 4,
				formats: {
					LTS: 'HH:mm:ss',
					LT: 'HH:mm',
					L: 'DD.MM.YYYY',
					LL: 'D. MMMM YYYY',
					LLL: 'D. MMMM YYYY HH:mm',
					LLLL: 'dddd, D. MMMM YYYY HH:mm',
				},
				relativeTime: {
					future: 'in %s',
					past: 'vor %s',
					s: 'ein paar Sekunden',
					m: ['eine Minute', 'einer Minute'],
					mm: '%d Minuten',
					h: ['eine Stunde', 'einer Stunde'],
					hh: '%d Stunden',
					d: ['ein Tag', 'einem Tag'],
					dd: ['%d Tage', '%d Tagen'],
					M: ['ein Monat', 'einem Monat'],
					MM: ['%d Monate', '%d Monaten'],
					y: ['ein Jahr', 'einem Jahr'],
					yy: ['%d Jahre', '%d Jahren'],
				},
			},
		}
		state = GSTC.api.stateFromConfig(config)
		gstc = GSTC({
			element: this.$refs.gstc,
			state,
		})
	},
	beforeUnmount() {
		if (gstc) gstc.destroy()
	},
	methods: {
		// generates and returns rows for each analyst
		generateRows() {
			const rows = []
			const openRow = {
				id: '-1',
				label: t('shifts', 'Offene Schichten'),
				style: { background: '#d3d7de' },
			}
			rows.push(openRow)
			this.analysts.forEach((analyst) => {
				let id = analyst.uid
				id = id.replaceAll('.', '_')
				const date = GSTC.api.date(this.date).startOf('week').add(1, 'days').format('YYYY-MM-DD')
				const row = {
					id,
					label: analyst.name,
					analyst,
					date,
				}
				this.shiftsTypes.forEach((shiftsType) => {
					if (shiftsType.isWeekly === '1') {
						if (shiftsType.moRule === '0' || shiftsType.moRule === '1') {
							const checked = this.shifts.find((shift) => {
								return shift.shiftTypeId === shiftsType.id.toString() && shift.userId === analyst.uid
							})
							row[shiftsType.name] = ({ row, vido }) => {
								// eslint-disable-next-line multiline-ternary
								return vido.html`<div class="${checked ? 'weekly-indicator' : ''}" style="background-color: ${checked ? shiftsType.color : ''}"> ${this.isAdmin ? vido.html`<input type="radio" id="${shiftsType.id}" name="${shiftsType.name}" value="${id + shiftsType.id}"
												@click="${() => { this.onRadioButtonClick(row, shiftsType, checked) }}"
												.checked=${checked}></input>` : ''}</div>`
							}
						} else {
							const checked = this.shifts.find((shift) => {
								return shift.shiftTypeId === shiftsType.id.toString() && shift.userId === analyst.uid
							})
							if (!this[shiftsType.name] || this[shiftsType.name].length === 0 || this.dateChanged) {
								this[shiftsType.name] = this.shifts.filter((shift) => {
									return shift.shiftTypeId === shiftsType.id.toString() && shift.date === date
								})
								this.dateChanged = false
							}
							row[shiftsType.name] = ({ row, vido }) => {
								// eslint-disable-next-line multiline-ternary
								return vido.html`<div class="${checked ? 'weekly-indicator' : ''}" style="background-color: ${checked ? shiftsType.color : ''}"> ${this.isAdmin ? vido.html`<input type="checkbox" id="${shiftsType.id}" name="${shiftsType.name}" value="${id + shiftsType.id}"
												@click="${() => { this.onCheckBoxButtonClick(row, shiftsType, checked) }}"
												.checked=${checked}></input>` : ''}</div>`
							}
						}
					}
				})
				rows.push(row)
			})
			return rows
		},
		// generates and returns item for each shift
		generateItems() {
			const items = []
			this.shifts.forEach((shift, index) => {
				const start = GSTC.api.date(shift.date)
				const id = shift.id
				let rowId = shift.userId
				rowId = rowId.replaceAll('.', '_')
				const shiftsType = shift.shiftsType
				if (shiftsType.isWeekly === '0') {
					items.push({
						id,
						label: this.generateItemLabel,
						rowId,
						time: {
							start: start.valueOf(),
							end: start.endOf('day').valueOf(),
						},
						minWidth: 200,
						style: {
							background: shiftsType.color,
						},
					})
				}
			})
			return items
		},
		generateItemLabel({ item, vido }) {
			const shiftId = GSTC.api.sourceID(item.id)
			const shift = this.$store.getters.getShiftById(shiftId)
			const shiftsType = this.$store.getters.getShiftsTypeById(shift.shiftTypeId)
			if (this.isAdmin) {
				return vido.html`<div class="gstc_items" style="cursor:pointer;">${shiftsType.name}
				<i class="v-icon icon icon-delete" @click="${() => this.onItemClick(item)}"></i></div>`
			} else {
				return vido.html`<div class="gstc_items" style="cursor:pointer;">${shiftsType.name}</div>`
			}
		},
		onItemClick(item) {
			this.$store.dispatch('deleteAssignment', GSTC.api.sourceID(item.id))
		},
		onRadioButtonClick(row, shiftsType, checked) {
			if (!checked) {
				const oldShift = this.shifts.find((shift) => {
					return shift.date === row.date && shift.shiftTypeId === shiftsType.id.toString()
				})
				const newShift = {
					id: oldShift.id,
					analystId: row.analyst.uid,
					shiftTypeId: shiftsType.id,
					date: row.date,
				}
				this.$store.dispatch('updateShift', newShift)
			}
		},
		onCheckBoxButtonClick(row, shiftsType, checked) {
			if (!checked) {
				const currShift = this[shiftsType.name].shift()
				const newShift = {
					id: currShift.id,
					userId: row.analyst.uid,
					shiftTypeId: shiftsType.id,
					date: row.date,
				}
				this.$store.dispatch('updateShift', {
					id: newShift.id,
					analystId: newShift.userId,
					shiftTypeId: newShift.shiftTypeId,
					date: newShift.date,
				})
				this[shiftsType.name].push(newShift)
			}
		},
		// changes the Calendar Timespan to Month or Week
		async updateCalendar(format) {
			this.dateChanged = true
			await this.$store.commit('updateDisplayedDateFormat', format)
			const date = this.date
			// updating the state of the calendar
			state.update('config.chart.time', (time) => {
				time.from = date.startOf(format).valueOf()
				time.to = date.endOf(format).valueOf()
				return time
			})
		},
		// changes the time of calendar to current timespan including today
		async setToday() {
			const today = dayjs()
			this.dateChanged = true
			await this.$store.commit('updateDisplayedDate', today)
			// updating the state of the calendar
			state.update('config.chart.time', (time) => {
				time.from = today.startOf(this.selectedCalendarFormat).valueOf()
				time.to = today.endOf(this.selectedCalendarFormat).valueOf()
				return time
			})
		},
		// move to previous timeinterval with given calendarformat
		async prev() {
			let date = this.date
			// updating the state of the calendar
			date = date.add(-1, this.selectedCalendarFormat)
			this.dateChanged = true
			await this.$store.commit('updateDisplayedDate', date)
			state.update('config.chart.time', (time) => {
				time.from = date.startOf(this.selectedCalendarFormat).valueOf()
				time.to = date.endOf(this.selectedCalendarFormat).valueOf()
				return time
			})
		},
		// move to next timeinterval with given calendarformat
		async next() {
			let date = this.date
			date = date.add(1, this.selectedCalendarFormat)
			this.dateChanged = true
			// updating the state of the calendar
			await this.$store.commit('updateDisplayedDate', date)
			state.update('config.chart.time', (time) => {
				time.from = date.startOf(this.selectedCalendarFormat).valueOf()
				time.to = date.endOf(this.selectedCalendarFormat).valueOf()
				return time
			})
		},
	},
}
</script>

<style>
.gstc-component {
	margin: 0;
	padding: 0;
}

.toolbox {
	padding: 10px;
}
</style>
