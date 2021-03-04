<template>
	<div>
		<div ref="gstc" class="gstc-wrapper" />
	</div>
</template>

<script>
import GSTC from 'gantt-schedule-timeline-calendar'
import { Plugin as TimeLinePointer } from 'gantt-schedule-timeline-calendar/dist/plugins/timeline-pointer.esm.min.js'
import { Plugin as Selection } from 'gantt-schedule-timeline-calendar/dist/plugins/selection.esm.min.js'
import { Plugin as ItemMovement } from 'gantt-schedule-timeline-calendar/dist/plugins/item-movement.esm.min.js'
import 'gantt-schedule-timeline-calendar/dist/style.css'
import { getYYYYMMDDFromDate } from '../utils/date'

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
		const today = GSTC.api.date(getYYYYMMDDFromDate(new Date()))
		const config = {
			licenseKey: '====BEGIN LICENSE KEY====\\nV8zWVvgA1wKSVy/+L0kuD3vf/2wHxj6aOSJiiHox7NEDrQn/ZkhX+umaZwWa+BZyeAsbBMS2D9QffCcouGoEjd6zZ6TKg1czvQGs9x+eQJvmZVttYDyNawEgVTVwRGV9K/Qcmc5fG6R9ZOpjHZVGLS1awi01kt6zu21IOyxCZKLXz4fnCfiLpplkjclMLJxBbFud0sa1fUpKwEtZpw1kW+UN3saFnesar4oepA2RMM/3FofbKRALa2qsMbOdAlEE6UEPYi0htImFUg01qISsGZfXmQ8i/4Na/S5aoUAfWKoI1NcOZ3xF1tnIMYIkJSXss6v24oeeu+MlIMydxMGnaw==||U2FsdGVkX1+qbfbvzH+haFNfV1T1S/m3Hv8UbDUTXL+KQxlOlSZ9bIGaMYnMw6pfP17wHzHvKSzflwCZS2S3OupgS8Vf+7HAEujkKjdh5Rw=\\nIPM1F53nZFXPaGSRHqUPk11mQ/KzcyDlcPYs9QgQ3JdG84twvjKNrirKZ+4N55aNZUrG0Wy4ffJr81XmPAgOMkSr4TX7lvhqQz0TkZ/C70BVevOxB+grlbTT1XaQMxvPK7ouQ4M/nToodmYLZCZ5z3tpZs0p2LjRx8CDvYBLvd2XnjU6ky1R8CXUm9F45j1HDody9dJ/dX/xpOqQ0VzeRO9zKGuZjDtTYYAyBLHnqTvZnJ3M78GkHaV/uNQeWqwmW3Kg2HQ0pFv95tLF3JL/5nvVZxevGWHQMYf+BJhez+mQSmqaTZPhHKuobb4SFE2tTXi+2gjjjx5jaTF6RNVYmQ==\\n====END LICENSE KEY====',
			plugins: [TimeLinePointer(), Selection(), ItemMovement()],
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
