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
					v-model="selectedCalendarFormat"
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
import 'gantt-schedule-timeline-calendar/dist/style.css'
import { getYYYYMMDDFromDate } from '../utils/date'
import { translate } from '@nextcloud/l10n'

let gstc, state

function getBackground(shiftType) {
	const hash = hashCode(shiftType)

	const c = (hash & 0x00FFFFFF)
		.toString(16)
		.toUpperCase()

	return '#' + '00000'.substring(0, 6 - c.length) + c
}
function hashCode(string) {
	let hash = 0
	let i
	let chr
	if (string.length === 0) return hash
	for (i = 0; i < string.length; i++) {
		chr = string.charCodeAt(i)
		hash = ((hash << 5) - hash) + chr
		hash |= 0
	}
	return hash
}
function getMonthTranslated() {
	return translate('shifts', 'Monat')
}
function getWeekTranslated() {
	return translate('shifts', 'Woche')
}

export default {
	name: 'Calendar',
	props: {
		analysts: {
			type: Array,
			required: true,
		},
		shifts: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			currentShifts: [],
			date: GSTC.api.date(),
			typeToLabel: {
				month: getMonthTranslated(),
				week: getWeekTranslated(),
			},
			selectedCalendarFormat: 1,
			calendarFormats: [
				{
					value: 1,
					text: getMonthTranslated(),
				},
				{
					value: 2,
					text: getWeekTranslated(),
				},
			],
		}
	},
	watch: {
		shifts: {
			handler(newVal) {
				const difference = newVal
					.filter(x => !this.currentShifts.includes(x))
					.concat(this.currentShifts.filter(x => !newVal.includes(x)))
				difference.forEach((shift) => {
					const start = GSTC.api.date(shift.date)
					const id = GSTC.api.GSTCID(shift.id)
					const rowId = GSTC.api.GSTCID(shift.userId)
					const newItem = {
						id,
						label: `${shift.shiftsType.name}`,
						rowId,
						time: {
							start: start.valueOf(),
							end: start.endOf('day').valueOf(),
						},
						style: {
							background: getBackground(shift.shiftsType.name),
						},
					}
					state.update(`config.chart.items.${id}`, newItem)
				})
				this.currentShifts = newVal.slice()
			},
			deep: true,
		},
	},
	mounted() {
		this.currentShifts = this.shifts.slice()
		const today = GSTC.api.date()
		const config = {
			licenseKey: '====BEGIN LICENSE KEY====\\nV8zWVvgA1wKSVy/+L0kuD3vf/2wHxj6aOSJiiHox7NEDrQn/ZkhX+umaZwWa+BZyeAsbBMS2D9QffCcouGoEjd6zZ6TKg1czvQGs9x+eQJvmZVttYDyNawEgVTVwRGV9K/Qcmc5fG6R9ZOpjHZVGLS1awi01kt6zu21IOyxCZKLXz4fnCfiLpplkjclMLJxBbFud0sa1fUpKwEtZpw1kW+UN3saFnesar4oepA2RMM/3FofbKRALa2qsMbOdAlEE6UEPYi0htImFUg01qISsGZfXmQ8i/4Na/S5aoUAfWKoI1NcOZ3xF1tnIMYIkJSXss6v24oeeu+MlIMydxMGnaw==||U2FsdGVkX1+qbfbvzH+haFNfV1T1S/m3Hv8UbDUTXL+KQxlOlSZ9bIGaMYnMw6pfP17wHzHvKSzflwCZS2S3OupgS8Vf+7HAEujkKjdh5Rw=\\nIPM1F53nZFXPaGSRHqUPk11mQ/KzcyDlcPYs9QgQ3JdG84twvjKNrirKZ+4N55aNZUrG0Wy4ffJr81XmPAgOMkSr4TX7lvhqQz0TkZ/C70BVevOxB+grlbTT1XaQMxvPK7ouQ4M/nToodmYLZCZ5z3tpZs0p2LjRx8CDvYBLvd2XnjU6ky1R8CXUm9F45j1HDody9dJ/dX/xpOqQ0VzeRO9zKGuZjDtTYYAyBLHnqTvZnJ3M78GkHaV/uNQeWqwmW3Kg2HQ0pFv95tLF3JL/5nvVZxevGWHQMYf+BJhez+mQSmqaTZPhHKuobb4SFE2tTXi+2gjjjx5jaTF6RNVYmQ==\\n====END LICENSE KEY====',
			plugins: [TimeLinePointer()],
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
					from: today.startOf('month').valueOf(),
					to: today.endOf('month').valueOf(),
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
		generateRows() {
			const rows = []
			this.analysts.forEach((analyst) => {
				const id = analyst.uid
				rows.push({
					id,
					label: analyst.name,
				})
			})
			return rows
		},
		generateItems() {
			const items = []
			this.shifts.forEach((shift, index) => {
				const start = GSTC.api.date(shift.date)
				const id = shift.id
				const rowId = shift.userId
				const shiftsType = shift.shiftsType
				items.push({
					id,
					label: `${shiftsType.name}`,
					rowId,
					time: {
						start: start.valueOf(),
						end: start.endOf('day').valueOf(),
					},
					minWidth: 200,
					style: {
						background: getBackground(shiftsType.name),
					},
				})
			})
			return items
		},
		updateCalendar() {
			if (this.date.day() === 0) {
				this.date = this.date.add(-1, 'week')
			}
			let startOf, endOf
			switch (this.selectedCalendarFormat) {
			case 1:
				startOf = this.date.startOf('month').valueOf()
				endOf = this.date.endOf('month').valueOf()
				break
			case 2:
				startOf = this.date.startOf('week').add(1, 'day').valueOf()
				endOf = this.date.endOf('week').add(1, 'day').valueOf()
				break
			default:
				startOf = this.date.startOf('month').valueOf()
				endOf = this.date.endOf('month').valueOf()
			}
			state.update('config.chart.time', (time) => {
				time.from = startOf
				time.to = endOf
				return time
			})
		},
		setToday() {
			const today = GSTC.api.date(getYYYYMMDDFromDate(new Date()))
			let startOf, endOf
			switch (this.selectedCalendarFormat) {
			case 1:
				startOf = today.startOf('month').valueOf()
				endOf = today.endOf('month').valueOf()
				break
			case 2:
				startOf = today.startOf('week').add(1, 'day').valueOf()
				endOf = today.endOf('week').add(1, 'day').valueOf()
				break
			default:
				startOf = today.startOf('month').valueOf()
				endOf = today.endOf('month').valueOf()
			}
			state.update('config.chart.time', (time) => {
				time.from = startOf
				time.to = endOf
				return time
			})
		},
		prev() {
			let startOf, endOf
			switch (this.selectedCalendarFormat) {
			case 1:
				this.date = this.date.add(-1, 'month')
				startOf = this.date.startOf('month').valueOf()
				endOf = this.date.endOf('month').valueOf()
				break
			case 2:
				this.date = this.date.add(-1, 'week')
				startOf = this.date.startOf('week').add(1, 'day').valueOf()
				endOf = this.date.endOf('week').add(1, 'day').valueOf()
				break
			default:
				this.date = this.date.add(-1, 'month')
				startOf = this.date.startOf('month').valueOf()
				endOf = this.date.endOf('month').valueOf()
			}
			state.update('config.chart.time', (time) => {
				time.from = startOf
				time.to = endOf
				return time
			})
		},
		next() {
			let startOf, endOf
			switch (this.selectedCalendarFormat) {
			case 1:
				this.date = this.date.add(1, 'month')
				startOf = this.date.startOf('month').valueOf()
				endOf = this.date.endOf('month').valueOf()
				break
			case 2:
				this.date = this.date.add(1, 'week')
				startOf = this.date.startOf('week').add(1, 'day').valueOf()
				endOf = this.date.endOf('week').add(1, 'day').valueOf()
				break
			default:
				this.date = this.date.add(1, 'month')
				startOf = this.date.startOf('month').valueOf()
				endOf = this.date.endOf('month').valueOf()
			}
			state.update('config.chart.time', (time) => {
				time.from = startOf
				time.to = endOf
				return time
			})
		},
		getMonthTranslated() {
			return translate('shifts', 'Monat')
		},
		getWeekTranslated() {
			return translate('shifts', 'Woche')
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
