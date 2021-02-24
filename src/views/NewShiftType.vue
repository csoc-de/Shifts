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

		<PropertyTitle
			:value="name"
			@update:value="updateName" />
		<PropertyDesc
			:value="description"
			icon="icon-menu"
			:readable-name=" $t('shifts','Description')"
			:placeholder="$t('shifts','Write Description here')"
			@update:value="updateDescription" />
		<!-- eslint-disable -->
		<vue-timepicker
			v-model="startTimestamp"
			@input="changeStartTimestamp"></vue-timepicker>
		<vue-timepicker
			v-model="stopTimestamp"
			@input="changeStopTimestamp"></vue-timepicker>
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
import PropertyDesc from '../components/Editor/Properties/PropertyDesc'
import PropertyTitle from '../components/Editor/Properties/PropertyTitle'
import PopoverLoadingIndicator from '../components/Popover/PopoverLoadingIndicator'
import VueTimepicker from 'vue2-timepicker'
import 'vue2-timepicker/dist/VueTimepicker.css'
export default {
	name: 'NewShiftType',
	components: {
		PropertyTitle,
		PropertyDesc,
		PopoverLoadingIndicator,
		VueTimepicker,
		ActionButton,
		Actions,
		EmptyContent,
	},
	data() {
		return {
			placement: 'auto',
			isLoading: false,
			isError: false,
			error: null,
			newShiftType: {
				name: '',
				description: '',
				startTimestamp: '00:00',
				stopTimestamp: '00:00',
			},
		}
	},
	computed: {
		name() {
			return this.newShiftType.name
		},
		description() {
			return this.newShiftType.description
		},
		startTimestamp: {
			get() {
				return this.newShiftType.startTimestamp
			},
			set(newValue) {
				this.newShiftType.startTimestamp = newValue
			},
		},
		stopTimestamp: {
			get() {
				return this.newShiftType.stopTimestamp
			},
			set(newValue) {
				this.newShiftType.stopTimestamp = newValue
			},
		},
	},
	mounted() {
		this.isLoading = false
	},
	methods: {
		closeEditor() {
			this.$emit('cancel')
		},
		async cancel() {
			if (this.isLoading) {
				return
			}

			this.closeEditor()
		},
		updateName(name) {
			this.newShiftType.name = name
		},
		updateDescription(description) {
			this.newShiftType.description = description
		},
		changeStartTimestamp(startTimestamp) {
			this.newShiftType.startTimestamp = startTimestamp
		},
		changeStopTimestamp(stopTimestamp) {
			this.newShiftType.stopTimestamp = stopTimestamp
		},
		save() {
			this.$emit('save', this.newShiftType)
			this.newShiftType = {
				name: '',
				description: '',
				startTimestamp: '00:00',
				stopTimestamp: '00:00',
			}
		},
	},
}
</script>
