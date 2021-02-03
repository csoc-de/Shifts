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

let gstc, state
function generateRows() {
	const rows = {}
	for (let index = 0; index < 10; ++index) {
		const id = GSTC.api.GSTCID(index.toString())
		rows[id] = {
			id,
			label: `Row ${index}`,
		}
	}
	return rows
}
function generateItems() {
	const items = {}
	let start = GSTC.api.date().startOf('day').subtract(6, 'day')
	for (let i = 0; i < 100; i++) {
		const id = GSTC.api.GSTCID(i.toString())
		const rowId = GSTC.api.GSTCID((Math.floor(Math.random() * 10)).toString())
		start = start.add(1, 'day')
		items[id] = {
			id,
			label: `Item ${i}`,
			rowId,
			time: {
				start: start.valueOf(),
				end: start.endOf('day').valueOf(),
			},
		}
	}
	return items
}

export default {
	name: 'Calendar',
	mounted() {
		const config = {
			licenseKey: '====BEGIN LICENSE KEY====\\navmUbYOBJJQTbPqKzglx+pD9L4wzHfJIE6LW4LXOOVsKzjPTHZejg6f47UyJrTjDvVpk1EqhdhR3xSzHmnI9MlYyg+xmqh7XmPv+evL7Xs5KnQmO7tma3GpHP9+IKMyPcaKb56GM+Y4DHwCn+AWcT/5KIZ3st8AyH/bvarWsyZnKyKbDR/D/byxLVV2inuLi55CNPwqBbmaQwTwix3DLdpRY9LrTuN2GxRwBdj02I78wQEyw8D8uJjSIAsJ7/9pZpQaMKmCVYdaVd/tMYm+LDBCxLPYIgQ0+f0QXbWLhwIxLjZT7T+hgxij/W3bARpfhpND+UlntBXyF02qLBU8ETw==||U2FsdGVkX1+gMjgB+GK2W/+cQ/JFsPpcewUdI8eIqA+qbYM+N8XWWFjxfjvmQr4g8qggXZV0l3ArT8A3l6Ib6tZBAC4u+T38wpSpeTW581E=\\nsQXfjVJtIl9hd9HAXYawCwLOye+yJWgxkB0sMGcGFpuaG1Gsp73bCMRqY4sEBiJOblLBb07k4xg+ZfgLHAui/DjI3fezDxDlWOinfOZawQcrydGn+WOvknd9FMotwxI5JsISa6Y/p2lpOuAF2nyrQRgUde04TXPKomf7+uuP/314pidXoVE9x0OY1Aysd2S69JGPly8OSEewvAz9v6cP2ET4MGIFsxyyhpu0CnyEnNlgeQLpYctRAoVQ5fPi3mD8yXqbNR6NuPWtO9qrw2VgYgf0+X6tupt9ElHvErsJ2Ww0fpfdgRAgaG2AfCg1/MGpYqDi6/IH3xfjLltm6QPqKw==\\n====END LICENSE KEY====',
			plugins: [TimeLinePointer(), Selection(), ItemMovement()],
			list: {
				columns: {
					data: {
						[GSTC.api.GSTCID('id')]: {
							id: GSTC.api.GSTCID('id'),
							width: 200,
							data: ({ row }) => GSTC.api.sourceID(row.id),
							header: {
								content: 'ID',
							},
						},
						[GSTC.api.GSTCID('id')]: {
							id: GSTC.api.GSTCID('id'),
							width: 200,
							data: 'label',
							header: {
								content: 'Analyst',
							},
						},
					},
				},
				rows: generateRows(),
			},
			chart: {
				items: generateItems(),
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
