<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -->

<template>
	<div class="tab_content">
		<!--eslint-disable-->
		<v-btn
			color="light-blue"
			@click="openDialog()">
			{{ t('shifts','Add new Shiftstype') }}
		</v-btn>
		<ShiftsTypeModal v-if="dialogOpen"
			:shifts-type="shiftsTypes"
			@close="closeDialog"
			@saved="dialogSaved" />

		<h1>{{ t('shifts', 'Shiftstype') }}</h1>
		<v-list>
			<v-list-item-group>
				<v-list-item
					v-for="(item, i) in shiftsTypes"
					:key="item.id">
					<v-list-item-title v-text="item.name"></v-list-item-title>
					<v-list-item-action>
						<v-dialog
							v-model="removeShiftsTypeDialogs[item.id]"
							width="500">
							<template v-slot:activator="{ on, attrs }">
								<v-btn
									color="red lighten-1"
									dark
									v-bind="attrs"
									v-on="on">
									{{ t('shifts', 'Delete') }}
								</v-btn>
							</template>
							<v-card>
								<v-card-text>
									{{ t('shifts', 'Are you sure that u want to delete the Shiftstype and all its Shifts') }}
								</v-card-text>
								<v-card-actions>
									<v-spacer></v-spacer>
									<v-btn
										color="red lighten-1"
										dark
										@click="deleteShiftsType(item)">
										{{ t('shifts', 'Delete') }}
									</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
						<v-btn
							color="light-blue"
							@click="openEditDialog(item)">
							{{ t('shifts', 'Edit') }}
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
			removeShiftsTypeDialogs: {},
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
			this.removeShiftsTypeDialogs[shiftsType.id] = false
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
