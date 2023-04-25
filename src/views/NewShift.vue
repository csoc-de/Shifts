<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<!--
  - Dialog to add new Shift
  -->
<template>
	<NcModal
		size="large"
		@close="close"
		:title="t('shifts','Add Shift')">

		<div class="container">
			<div class="row">
				<h3>{{ t('shifts','Add Shift') }}</h3>
			</div>

			<div class="row">
				<div class="col">
					<NcMultiselect
						v-model="selectedShiftType"
						class="invitees-search"
						open-direction="bottom"
						:options="shiftsTypes"
						track-by="id"
						label="name" />
				</div>
				<div class="col">
					<NcMultiselect
						v-model="selectedAnalysts"
						:options="formattedOptions"
						label="displayName"
						track-by="email"
						multiple
						:user-select="true">
						<template #singleLabel="{ option }">
							<NcListItemIcon
								v-bind="option"
								:avatar-size="24"
								:no-margin="true" />
						</template>
					</NcMultiselect>
				</div>
			</div>

			<div class="row">
				<div class="col">
					<NcDatetimePicker
						range
						appendToBody
						v-model="selectedDates"
						type="date" />
				</div>
			</div>
		</div>

		<!-- Action buttons -->
		<div class="buttons-bar">
			<div class="right">
				<NcButton
					type="secondary"
					@click="cancel">
					{{ t('shifts','Cancel') }}
				</NcButton>
				<NcButton
					type="primary"
					@click="save">
					{{ t('shifts','Save') }}
				</NcButton>
			</div>
		</div>
	</NcModal>
</template>

<script>
import dayjs from 'dayjs'
import store from '../store'
import { mapGetters, mapState } from 'vuex'
import { NcModal, NcMultiselect, NcDatetimePicker, NcButton } from '@nextcloud/vue'
export default {
	name: 'NewShift',
	components: {
		NcModal,
		NcButton,
		NcMultiselect,
		NcDatetimePicker,
	},
	data() {
		return {
			isLoading: true,
			dateMenu: false,

			selectedShiftType: {
				id: -1,
				name: t('shifts', 'Select Shiftstype'),
			},
			selectedDates: [],
			selectedAnalysts: [],
			formattedOptions: [],
		}
	},
	computed: {
		...mapGetters({
			allAnalysts: 'allAnalysts',
			shiftsTypes: 'allShiftsTypes',
		}),
		...mapState({
			newShiftInstance: (state) => state.newShiftInstance.newShiftInstance,
		}),
		allowedDates() {
			if (this.newShiftInstance.shiftsType.isWeekly === '0' || this.newShiftInstance.shiftsType.isWeekly === '') {
				return null
			} else {
				return val => new Date(val).getDay() === 1
			}
		},
	},
	beforeMount() {
		this.formattedOptions = this.allAnalysts.map(analyst => {
			let displayName
			if (analyst.name && analyst.email === undefined) {
				displayName = analyst.name
			} else if (analyst.name && analyst.email) {
				displayName = `${analyst.name} (${analyst.email})`
			} else {
				displayName = analyst.email
			}

			return {
				uid: analyst.uid,
				name: analyst.name,
				email: analyst.email,
				skillGroup: analyst.skillGroup,
				displayName
			}
		})
	},
	mounted() {
		this.isLoading = false
	},
	methods: {
		async cancel() {
			if (this.isLoading) {
				return
			}
			this.dateMenu = false
			this.close()
		},
		updateShiftType(shiftType) {
			this.$store.commit('changeShiftsType', shiftType)
		},
		save() {
			const dates = []
			let startDate = dayjs(this.selectedDates[0])
			const endDate = dayjs(this.selectedDates[1])
			let numDays = endDate.diff(startDate, 'day', true)
			while (numDays > 0) {
				dates.push(startDate)
				startDate = startDate.add(1, 'day')
				numDays--
			}
			dates.push(endDate)

			for (const analyst in this.selectedAnalysts) {
				for (const date in dates) {
					store.dispatch('createNewShift', {
						// $analystId, $shiftTypeId, $date
						analystId: this.selectedAnalysts[analyst].uid,
						shiftTypeId: this.selectedShiftType.id,
						date: dates[date].format('YYYY-MM-DD')
					})
				}
			}
		},
		close() {
			this.$emit('close')
		},
	},
	watch: {
		analystValue: {
			handler(newValue, oldValue) {
				for (const v in oldValue) {
					oldValue[v].icon = ''
				}
				for (const v in newValue) {
					newValue[v].icon = 'icon-user'
				}
			}
		},
	}
}
</script>

<style lang="scss" scoped>
h3 {
	font-weight: bold;
}
</style>
