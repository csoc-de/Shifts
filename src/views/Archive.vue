<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@outlook.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@outlook.de>
  -->

<template>
	<div class="tab_content">
		<!--eslint-disable-->
		<v-menu
			ref="menu"
			v-model="menu"
			:close-on-content-click="false"
			:return-value.sync="tempDates"
			transition="scale-transition"
			offset-y
			max-width="290px"
			min-width="290px">
			<template v-slot:activator="{ on, attrs }">
				<v-text-field
					v-model="dateRangeText"
					:label="t('shifts', 'Zeitspanne')"
					prepend-icon="icon-calendar-dark"
					readonly
					v-bind="attrs"
					v-on="on">
				</v-text-field>
			</template>
			<v-date-picker
				v-model="tempDates"
				range
				no-title
				scrollable>
				<v-spacer></v-spacer>
				<v-btn
					text
					color="primary"
					@click="menu = false">
					{{ t('shifts', 'Abbrechen') }}
				</v-btn>
				<v-btn
					text
					color="primary"
					@click="rangeChanged(tempDates)">
					{{ t('shifts', 'Ok') }}
				</v-btn>
			</v-date-picker>
		</v-menu>
		<v-data-table
			v-if="!loading && !loadingArchive"
			:headers="headers"
			:items="shiftData"
			disable-pagination>
		</v-data-table>
		<!-- eslint-enable-->
	</div>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
	name: 'Archive',
	data() {
		return {
			menu: false,
			items: [],
			tempDates: [],
		}
	},
	computed: {
		...mapGetters({
			shiftTypes: 'allShiftsTypes',
			shiftData: 'currentShiftsData',
			analysts: 'allAnalysts',
			loading: 'loading',
			loadingArchive: 'archiveLoading',
			dates: 'currentDates',
		}),
		dateRangeText() {
			return this.dates.join('~')
		},
		headers() {
			const val = [
				{
					text: t('shifts', 'Analyst'),
					value: 'userName',
				},
			]
			this.shiftTypes.forEach(shiftType => {
				val.push({
					text: shiftType.name,
					value: shiftType.id.toString(),
				})
			})
			return val
		},
	},
	methods: {
		rangeChanged(dates) {
			this.$refs.menu.save(dates)
			this.$store.commit('updateDates', dates)
			this.$store.dispatch('fetchArchiveData', dates)
		},
	},
}
</script>
