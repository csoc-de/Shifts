<!--
  - Dialog to add new Shift
  -->
<template>
	<div>
		<Multiselect v-model="value1"
			:options="shiftsTypes"
			track-by="id"
			label="name"
			@change="updateShiftType" />
		<div class="app-sidebar-tab__content">
			<AnalystsList
				v-if="!isLoading"
				:is-read-only="false"
				:new-shift-instance="newShiftInstance" />
		</div>
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
					scrollable
					locale="de-DE"
					first-day-of-week="1">
				<v-spacer></v-spacer>
				<v-btn color="primary" @click="cancel()">
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
import { mapGetters, mapState } from 'vuex'
export default {
	name: 'NewShift',
	components: {
		AnalystsList,
		Multiselect,
	},
	data() {
		return {
			isLoading: true,
			value1: {
				id: -1,
				name: t('shifts', 'Schichtauswahl'),
			},
			dateMenu: false,
		}
	},
	computed: {
		...mapGetters({
			shiftsTypes: 'allShiftsTypes',
		}),
		...mapState({
			newShiftInstance: (state) => state.newShiftInstance.newShiftInstance,
		}),
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
			this.dateMenu = false
			this.closeEditor()
		},
		updateShiftType(shiftType) {
			this.$store.commit('changeShiftsType', shiftType)
		},
		save() {
			this.$store.dispatch('saveNewShift', {})
		},
	},
}
</script>
