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
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import dayOfYear from 'dayjs/plugin/dayOfYear'
import dayjs from 'dayjs'
import 'dayjs/locale/de'

let gstc, state

function getMonthTranslated() {
	return translate('shifts', 'Monat')
}
function getWeekTranslated() {
	return translate('shifts', 'Woche')
}
function isCollision(movedShift) {
	const allItems = gstc.api.getAllItems()
	for (const currentShiftId in allItems) {
		if (currentShiftId === movedShift.id) continue
		const currentShift = allItems[currentShiftId]
		if (currentShift.rowId === movedShift.rowId) {
			if (
				movedShift.time.start >= currentShift.time.start && movedShift.time.start <= currentShift.time.end
			) {
				return true
			}
			if (
				movedShift.time.end >= currentShift.time.start && movedShift.time.end <= currentShift.time.end
			) {
				return true
			}
			if (
				movedShift.time.start <= currentShift.time.start && movedShift.time.end >= currentShift.time.end
			) {
				return true
			}
			if (
				movedShift.time.start >= currentShift.time.start && movedShift.time.end <= currentShift.time.end
			) {
				return true
			}
		}
	}
	return false
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
	},
	watch: {
		// watches or changes in the shifts Array to update the Calendar
		shifts: {
			handler(newVal, oldVal) {
				state.update('config', config => {
					config.chart.items = GSTC.api.fromArray(this.generateItems())
					return config
				})
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
						if (isCollision(myItem)) {
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
						const newAnalystId = GSTC.api.sourceID(afterItem.rowId)

						const newDate = GSTC.api.date(afterItem.time.start).format('YYYY-MM-DD')

						const oldShift = store.getters.getShiftById(shiftsId)
						const newShift = {
							id: shiftsId,
							userId: newAnalystId,
							shiftTypeId: oldShift.shiftTypeId,
							date: newDate,
						}
						Promise.all([
							axios.put(generateUrl(`/apps/shifts/shifts/${shiftsId}`), newShift),
						]).then(values => {
							console.log(values)
							store.dispatch('updateShifts')
						})

						return afterItem
					})
				},
			},
			snapToTime: {
				start({ startTime, time }) {
					return startTime.startOf('day')
				},
			},
		}
		if (this.isAdmin) {
			plugins.push(Selection())
			plugins.push(MovementPlugin(movementPluginConfig))
		}
		// config for the GSTC Calendar
		const config = {
			licenseKey: '====BEGIN LICENSE KEY====\\nV8zWVvgA1wKSVy/+L0kuD3vf/2wHxj6aOSJiiHox7NEDrQn/ZkhX+umaZwWa+BZyeAsbBMS2D9QffCcouGoEjd6zZ6TKg1czvQGs9x+eQJvmZVttYDyNawEgVTVwRGV9K/Qcmc5fG6R9ZOpjHZVGLS1awi01kt6zu21IOyxCZKLXz4fnCfiLpplkjclMLJxBbFud0sa1fUpKwEtZpw1kW+UN3saFnesar4oepA2RMM/3FofbKRALa2qsMbOdAlEE6UEPYi0htImFUg01qISsGZfXmQ8i/4Na/S5aoUAfWKoI1NcOZ3xF1tnIMYIkJSXss6v24oeeu+MlIMydxMGnaw==||U2FsdGVkX1+qbfbvzH+haFNfV1T1S/m3Hv8UbDUTXL+KQxlOlSZ9bIGaMYnMw6pfP17wHzHvKSzflwCZS2S3OupgS8Vf+7HAEujkKjdh5Rw=\\nIPM1F53nZFXPaGSRHqUPk11mQ/KzcyDlcPYs9QgQ3JdG84twvjKNrirKZ+4N55aNZUrG0Wy4ffJr81XmPAgOMkSr4TX7lvhqQz0TkZ/C70BVevOxB+grlbTT1XaQMxvPK7ouQ4M/nToodmYLZCZ5z3tpZs0p2LjRx8CDvYBLvd2XnjU6ky1R8CXUm9F45j1HDody9dJ/dX/xpOqQ0VzeRO9zKGuZjDtTYYAyBLHnqTvZnJ3M78GkHaV/uNQeWqwmW3Kg2HQ0pFv95tLF3JL/5nvVZxevGWHQMYf+BJhez+mQSmqaTZPhHKuobb4SFE2tTXi+2gjjjx5jaTF6RNVYmQ==\\n====END LICENSE KEY====',
			plugins,
			innerHeight: 600,
			list: {
				columns: {
					data: {
						[GSTC.api.GSTCID('id')]: {
							id: GSTC.api.GSTCID('id'),
							width: 100,
							data: 'label',
							header: {
								content: 'Analyst',
							},
						},
					},
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
			rows.push({
				id: '-1',
				label: t('shifts', 'Offene Schichten'),
			})
			this.analysts.forEach((analyst) => {
				let id = analyst.uid
				id = id.replaceAll('.', '-')
				rows.push({
					id,
					label: analyst.name,
				})
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
				rowId = rowId.replaceAll('.', '-')
				const shiftsType = shift.shiftsType
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
			this.$store.dispatch('deleteShift', GSTC.api.sourceID(item.id))
		},
		// changes the Calendar Timespan to Month or Week
		async updateCalendar(format) {
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
