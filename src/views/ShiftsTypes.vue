<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<template>
	<div>
		<div class="top-bar">
			<div class="left">
				<div class="buttons-bar">
					<NcButton
						type="primary"
						@click="openDialog()">
						{{ t('shifts','Add new Shiftstype') }}
					</NcButton>
				</div>
			</div>
		</div>
		<!--eslint-disable-->
		<ShiftsTypeModal
			v-if="dialogOpen"
			@close="closeDialog" />


		<div class="content">
			<div class="header">
				{{ t('shifts', 'Shiftstype') }}
			</div>

			<!-- Calendar -->
			<div class="body">
				<div
					v-for="item in shiftsTypes"
					:key="item.id"
					class="shiftTypeItem"
				>
					<div class="header">
						<h3>{{item.name}}</h3>
					</div>
					<div class="actions">
						<div class="buttons-bar">
							<div class="right">
								<NcButton
									color="light-blue"
									@click="openEditDialog(item)">
									{{ t('shifts', 'Edit') }}
								</NcButton>
								<NcButton
									@click="deleteShiftsType(item)"
									:disabled="item.deleted === '1'"
									type="error">
									<template #icon>
										<Delete :size="20" />
									</template>
								</NcButton>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--eslint-enable-->
	</div>
</template>

<script>
import store from '../store'
import { mapGetters } from 'vuex'
import { NcButton } from '@nextcloud/vue'
import Delete from 'vue-material-design-icons/Delete'
import ShiftsTypeModal from '../components/Modal/ShiftsTypeModal'

export default {
	name: 'ShiftsTypes',
	components: {
		Delete,
		NcButton,
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
			store.dispatch('createNewShiftsType')
			this.dialogOpen = true
		},
		openEditDialog(shiftsType) {
			store.dispatch('editExistingShiftsType', shiftsType)
			this.dialogOpen = true
		},
		deleteShiftsType(shiftsType) {
			this.removeShiftsTypeDialogs[shiftsType.id] = false
			store.dispatch('deleteShiftsType', shiftsType).finally(() => {
				store.dispatch('updateShiftsTypes')
				store.dispatch('updateShifts')
			})
		},
		closeDialog() {
			this.dialogOpen = false
		},
	},
}
</script>

<style lang="scss" scoped>
.content {
	.body {
		height: 100%;
		max-width: 100%;
	}
}

.shiftTypeItem {
	display: flex;
	flex-direction: row;

	padding: 12px;

	.header {
		flex-grow: 1;
		flex-basis: 0;
		max-width: 100%;
		justify-content: flex-start;
	}

	.actions {
		flex: 0 0 auto;
		justify-content: flex-end;
		margin-right: 14px;
	}
}

.shiftTypeItem:hover {
	background: lightgrey;
}
</style>
