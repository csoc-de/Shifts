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
			licenseKey: '====BEGIN LICENSE KEY====\\navmUbYOBJJQTbPqKzglx+pD9L4wzHfJIE6LW4LXOOVsKzjPTHZejg6f47UyJrTjDvVpk1EqhdhR3xSzHmnI9MlYyg+xmqh7XmPv+evL7Xs5KnQmO7tma3GpHP9+IKMyPcaKb56GM+Y4DHwCn+AWcT/5KIZ3st8AyH/bvarWsyZnKyKbDR/D/byxLVV2inuLi55CNPwqBbmaQwTwix3DLdpRY9LrTuN2GxRwBdj02I78wQEyw8D8uJjSIAsJ7/9pZpQaMKmCVYdaVd/tMYm+LDBCxLPYIgQ0+f0QXbWLhwIxLjZT7T+hgxij/W3bARpfhpND+UlntBXyF02qLBU8ETw==||U2FsdGVkX1+gMjgB+GK2W/+cQ/JFsPpcewUdI8eIqA+qbYM+N8XWWFjxfjvmQr4g8qggXZV0l3ArT8A3l6Ib6tZBAC4u+T38wpSpeTW581E=\\nsQXfjVJtIl9hd9HAXYawCwLOye+yJWgxkB0sMGcGFpuaG1Gsp73bCMRqY4sEBiJOblLBb07k4xg+ZfgLHAui/DjI3fezDxDlWOinfOZawQcrydGn+WOvknd9FMotwxI5JsISa6Y/p2lpOuAF2nyrQRgUde04TXPKomf7+uuP/314pidXoVE9x0OY1Aysd2S69JGPly8OSEewvAz9v6cP2ET4MGIFsxyyhpu0CnyEnNlgeQLpYctRAoVQ5fPi3mD8yXqbNR6NuPWtO9qrw2VgYgf0+X6tupt9ElHvErsJ2Ww0fpfdgRAgaG2AfCg1/MGpYqDi6/IH3xfjLltm6QPqKw==\\n====END LICENSE KEY====',
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
