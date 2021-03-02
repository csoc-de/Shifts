<template>
	<div v-if="isLoading">
		<PopoverLoadingIndicator />
	</div>

	<div v-else-if="isError">
		<div class="event-popover__top-right-actions">
			<Actions>
				<ActionButton
					v-close-popover
					icon="icon-close"
					@click="cancel">
					{{ $t('shifts', 'Close') }}
				</ActionButton>
			</Actions>
		</div>

		<EmptyContent icon="icon-shifts-dark">
			{{ $t('shifts', 'Shift does not exist') }}
			<template #desc>
				{{ error }}
			</template>
		</EmptyContent>
	</div>

	<div v-else>
		<div class="event-popover__top-right-actions">
			<Actions>
				<ActionButton
					icon="icon-close"
					@click="cancel">
					{{ $t('shifts', 'Close') }}
				</ActionButton>
			</Actions>
		</div>

		<div class="app-sidebar-tab__content">
			<AnalystsList
				v-if="!isLoading"
				:is-read-only="false"
				:new-shift-instance="newShiftInstance"
				@addAnalyst="addAnalyst"
				@removeAnalyst="removeAnalyst" />
		</div>
		<Multiselect v-model="value1"
			:options="shiftTypes"
			track-by="id"
			label="name"
			@change="updateShiftType" />
		<!-- eslint-disable -->
		<vc-date-picker
			ref="datePicker"
			:value="today"
			:attributes="attributes"
			:popover-keepVisibleOnInput="true"
			:popover-visibility="visibility"
			mode="date"
			is-inline
			@dayclick="onDayClick">
			<template v-slot="{ inputValue, togglePopover}">
				<div class="divOpenDatepicker">
					<button
						class="buttonOpenDatepicker"
						@click="togglePopover({ placement: 'auto-start'})">
						<svg viewBox="0 0 24 24" class="svgOpenDatepicker">
							<path fill="currentColor" d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z" />
						</svg>
					</button>
				</div>
			</template>
		</vc-date-picker>
		<!-- eslint-enable -->
		<button
			class="event-popover__buttons primary"
			@click="save">
			{{ $t('shifts', 'Save') }}
		</button>
	</div>
</template>

<script>
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
import AnalystsList from '../components/Editor/Analysts/AnalystsList'
import PopoverLoadingIndicator from '../components/Popover/PopoverLoadingIndicator'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
export default {
	name: 'NewShift',
	components: {
		Actions,
		ActionButton,
		EmptyContent,
		AnalystsList,
		PopoverLoadingIndicator,
		Multiselect,
	},
	props: {
		shiftTypes: {
			type: Array,
			default: () => [],
		},
	},
	data() {
		return {
			isLoading: true,
			isError: false,
			error: null,
			dateIsOpen: false,
			value1: {
				id: -1,
				name: 'Select ShiftType',
			},
			visibility: 'visible',
			newShiftInstance: {
				analysts: [],
				shiftsType: '',
				dates: [
					{
						date: new Date(),
					},
				],
			},
		}
	},
	computed: {
		shiftDate: {
			get() {
				return this.newShiftInstance.date
			},
			set(newValue) {
				this.newShiftInstance.date = newValue
			},
		},
		dates() {
			return this.newShiftInstance.dates.map(day => day.date)
		},
		attributes() {
			return this.dates.map(date => ({
				highlight: true,
				dates: date,
			}))
		},
		today() {
			return {
				highlight: false,
				dates: new Date(),
			}
		},
	},
	mounted() {
		this.isLoading = false
		this.$nextTick(() => {
			console.log(this.$refs.datePicker)
		})
	},
	methods: {
		onDayClick(date) {
			console.log(date)
			console.log(this.newShiftInstance.dates)
			const idx = this.newShiftInstance.dates.findIndex((d) => d.id === date.id)
			if (idx >= 0) {
				this.newShiftInstance.dates.splice(idx, 1)
			} else {
				this.newShiftInstance.dates.push({
					id: date.id,
					date: date.date,
				})
			}
			this.visibility = 'visible'
		},
		closeEditor() {
			this.$emit('cancel')
		},
		async cancel() {
			if (this.isLoading) {
				return
			}

			this.closeEditor()
		},
		addAnalyst(analyst) {
			this.newShiftInstance.analysts.push(analyst)
		},
		removeAnalyst(analyst) {
			const index = this.newShiftInstance.analysts.indexOf(analyst)
			this.newShiftInstance.analysts.splice(index, 1)
		},
		updateShiftType(shiftType) {
			this.newShiftInstance.shiftsType = shiftType
		},
		save() {
			this.$emit('save', this.newShiftInstance)
			this.newShiftInstance = {
				analysts: [],
				shiftsType: '',
				dates: [],
			}
			this.value1 = {
				id: -1,
				name: 'Select ShiftType',
			}
		},
	},
}
</script>
