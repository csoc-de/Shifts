<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@outlook.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@outlook.de>
  -->

<template>
	<div class="tab_content">
		<!--eslint-disable-->
		<v-btn
			color="light-blue"
			@click="openDialog()">
			{{ t('shifts','Neuen Schichttypen anlegen') }}
		</v-btn>
		<ShiftsTypeModal v-if="dialogOpen"
			:shifts-type="shiftsTypes"
			@close="closeDialog"
			@saved="dialogSaved" />

		<h1>{{ t('shifts', 'Schichttypen') }}</h1>
		<v-list>
			<v-list-item-group>
				<v-list-item
					v-for="(item, i) in shiftsTypes"
					:key="item.id">
					<v-list-item-title v-text="item.name"></v-list-item-title>
					<v-list-item-action>
						<v-btn
							color="red lighten-1"
							@click="deleteShiftsType(item)">
							{{ t('shifts', 'Enfernen') }}
						</v-btn>
						<v-btn
							color="light-blue"
							@click="openEditDialog(item)">
							{{ t('shifts', 'Bearbeiten') }}
						</v-btn>
					</v-list-item-action>
				</v-list-item>
			</v-list-item-group>
		</v-list>
		<!--eslint-enable-->
	</div>
</template>

<script>
import { mapGetters } from 'vuex'
import ShiftsTypeModal from '../components/Modal/ShiftsTypeModal'

export default {
	name: 'ShiftsTypes',
	components: {
		ShiftsTypeModal,
	},
	data() {
		return {
			dialogOpen: false,
		}
	},
	computed: {
		...mapGetters({
			shiftsTypes: 'allShiftsTypes',
		}),
	},
	methods: {
		openDialog() {
			this.dialogOpen = true
			this.$store.dispatch('createNewShiftsType')
		},
		openEditDialog(shiftsType) {
			this.dialogOpen = true
			this.$store.dispatch('editExistingShiftsType', shiftsType)
		},
		deleteShiftsType(shiftsType) {
			this.$store.dispatch('deleteShiftsType', shiftsType)
		},
		closeDialog() {
			this.dialogOpen = false
		},
		dialogSaved() {
			this.dialogOpen = false
		},
	},
}
</script>
