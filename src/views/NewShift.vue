<template>
	<div>
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
		<!--eslint-disable-->
		<v-menu
			ref="dateMenu"
			v-model="dateMenu"
			:close-on-content-click="false"
			:nudge-right="40"
			:return-value.sync="newShiftInstance.dates"
			transition="scale-transition"
			offset-y
			min-width="290px">
			<template v-slot:activator="{ on }">
				<v-combobox
					v-model="newShiftInstance.dates"
					multiple
					chips
					small-chips
					readonly
					v-on="on">
				</v-combobox>
			</template>
			<v-date-picker v-model="newShiftInstance.dates"
					multiple
					no-title
					scrollable>
				<v-spacer></v-spacer>
				<v-btn color="primary" @click="dateMenu = false">
					Cancel
				</v-btn>
				<v-btn color="primary" @click="$refs.dateMenu.save(newShiftInstance.dates)">
					Ok
				</v-btn>
			</v-date-picker>
		</v-menu>
		<v-btn color="primary" @click="cancel">
			Cancel
		</v-btn>
		<v-btn color="primary" @click="save">
			Save
		</v-btn>
		<!--eslint-enable-->
	</div>
</template>

<script>
import AnalystsList from '../components/Editor/Analysts/AnalystsList'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import { getYYYYMMDDFromDate } from '../utils/date'
export default {
	name: 'NewShift',
	components: {
		AnalystsList,
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
			value1: {
				id: -1,
				name: t('shifts', 'Schichtauswahl'),
			},
			dateMenu: false,
			newShiftInstance: {
				analysts: [],
				shiftsType: '',
				dates: [getYYYYMMDDFromDate(new Date())],
			},
		}
	},
	computed: {

	},
	mounted() {
		this.isLoading = false
	},
	methods: {
		onDayClick(date) {
			const idx = this.newShiftInstance.dates.findIndex((d) => d.id === date.id)
			if (idx >= 0) {
				this.newShiftInstance.dates.splice(idx, 1)
			} else {
				this.newShiftInstance.dates.push({
					id: date.id,
					date: date.date,
				})
			}
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
