<!--
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<template>
	<div class="archive">
		<table>
			<thead>
				<!-- Table header for date -->
				<tr>
					<th>
						{{ t('shifts','Analyst') }}
					</th>

					<th
						v-for="(header, i) in shiftTypes"
						:key="i">
						<span>{{ header.name }}</span>
					</th>
				</tr>
			</thead>

			<tbody>
				<tr
					v-for="(analyst, i) in analysts"
					:key="i">
					<td> {{ analyst.name }} </td>
					<td
						v-for="(header, j) in shiftTypes"
						:key="j">
						<span>{{ getShiftsForAnalystByShiftType(analyst.uid, header.id) }}</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
import { mapGetters } from 'vuex'
import store from '../../store'

export default {
	name: 'ArchiveContent',
	components: {
	},
	data() {
		return {
		}
	},
	methods: {
	},
	computed: {
		...mapGetters({
			analysts: 'allAnalysts',
			shiftTypes: 'allShiftsTypes',
			getShiftsForAnalystByShiftType: 'getShiftsForAnalystByShiftType',
		}),
	},
	mounted() {
		store.dispatch('setArchiveTimeRange', { timeRange: 6 })
	}
}
</script>

<style lang="scss" scoped>
.archive {
	max-width: 100%;
	margin-top: 25px;

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
		}
	}
}
</style>
