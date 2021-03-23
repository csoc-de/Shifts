<!--
  - Dialog to add new ShiftType
  -->
<template>
	<div>
		<PropertyTitle
			:value="name"
			@update:value="updateName" />
		<!-- eslint-disable -->
		<v-menu
			ref="startMenu"
			v-model="startMenu"
			:close-on-content-click="false"
			:nudge-right="40"
			:return-value.sync="newShiftType.startTimestamp"
			transition="scale-transition"
			offset-y
			max-width="290px"
			min-width="290px">
			<template v-slot:activator="{ on, attrs }">
				<v-text-field
					v-model="newShiftType.startTimestamp"
					label=" Start Time"
					v-bind="attrs"
					v-on="on">
				</v-text-field>
			</template>
			<v-time-picker
				v-if="startMenu"
				v-model="newShiftType.startTimestamp"
				format="24hr"
				full-width
				@click:minute="$refs.startMenu.save(newShiftType.startTimestamp)">
			</v-time-picker>
		</v-menu>
		<v-menu
			ref="stopMenu"
			v-model="stopMenu"
			:close-on-content-click="false"
			:nudge-right="40"
			:return-value.sync="newShiftType.stopTimestamp"
			transition="scale-transition"
			offset-y
			max-width="290px"
			min-width="290px">
			<template v-slot:activator="{ on, attrs }">
				<v-text-field
					v-model="newShiftType.stopTimestamp"
					label=" Stop Time"
					v-bind="attrs"
					v-on="on">
				</v-text-field>
			</template>
			<v-time-picker
				v-if="stopMenu"
				v-model="newShiftType.stopTimestamp"
				format="24hr"
				full-width
				@click:minute="$refs.stopMenu.save(newShiftType.stopTimestamp)">
			</v-time-picker>
		</v-menu>
		<!-- eslint-enable -->
		<v-btn color="primary" @click="cancel">
			Cancel
		</v-btn>
		<v-btn color="primary" @click="save">
			Save
		</v-btn>
	</div>
</template>

<script>
import PropertyTitle from '../components/Editor/Properties/PropertyTitle'
export default {
	name: 'NewShiftType',
	components: {
		PropertyTitle,
	},
	data() {
		return {
			isLoading: false,
			isError: false,
			error: null,
			startMenu: false,
			stopMenu: false,
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
