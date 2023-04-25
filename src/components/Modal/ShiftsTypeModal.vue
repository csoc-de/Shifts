<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<template>
	<NcModal
		size="large"
		@close="close"
		:title="t('shifts', 'New Shiftstype')">
		<div class="shifts-type-modal">
			<div class="container">
				<div class="row">
					<h3>Informationen</h3>
				</div>
				<div class="row">
					<div class="col">
						<NcTextField
							:value.sync="name"
							:label="t('shifts', 'New Shiftstype')" />
					</div>

					<div class="col">
						<NcMultiselect
							key="skillGroupSelect"
							:value.sync="skillGroupId"
							:options="skillGroups"
							track-by="id"
							label="name" />
					</div>
				</div>
			</div>

			<!-- Calendar color picker -->
			<div class="container">
				<div class="row">
					<h3>{{t('shifts', 'Calendar Color')}}</h3>
				</div>

				<div class="row">
					<div class="col">
						<div :style="{'background-color': color}" class="colorPreview"></div>
					</div>

					<div class="col">
						<NcColorPicker
							v-model="color"
							:advanced-fields="true">
							<NcButton>Farbe ändern</NcButton>
						</NcColorPicker>
					</div>
				</div>
			</div>

			<!-- Start and end time of shift -->
			<div class="container">
				<div class="row">
					<h3>Zeit</h3>
				</div>

				<div class="row">
					<div class="col">
						<NcCheckboxRadioSwitch :checked.sync="weekly">{{t('shifts','Weekly')}}</NcCheckboxRadioSwitch>
					</div>

					<div class="col" style="padding-left: 12px">
						<input
							type="number"
							min="-1"
							max="10"
							v-model="moRule"
							:disabled="!weekly"
							:placeholder="t('shifts', 'weekly Shifts')" />
					</div>
				</div>

				<div v-if="!weekly"
					class="row">
					<div class="col">
						<NcDatetimePicker
							clearable
							v-model="startTime"
							type="time"
							show-hour
							show-minute
							format="HH:mm"
							:show-second="false"
							:disabled="weekly"
							:placeholder="t('shifts', 'Start Time')" />
					</div>

					<div class="col">
						<NcDatetimePicker
							clearable
							v-model="stopTime"
							type="time"
							show-hour
							show-minute
							format="HH:mm"
							:show-second="false"
							:disabled="weekly"
							:placeholder="t('shifts', 'Stop Time')" />
					</div>
				</div>
			</div>

			<div class="container">
				<div class="row">
					<h3>{{ t('shifts', 'Rules') }}</h3>
				</div>

				<div class="row">
					<table class="ruleTable">
						<thead>
							<!-- Table header for date -->
							<tr v-if="!weekly">
								<th>{{t('shifts', 'Monday')}}</th>
								<th>{{t('shifts', 'Tuesday')}}</th>
								<th>{{t('shifts', 'Wednesday')}}</th>
								<th>{{t('shifts', 'Thursday')}}</th>
								<th>{{t('shifts', 'Friday')}}</th>
								<th>{{t('shifts', 'Saturday')}}</th>
								<th>{{t('shifts', 'Sunday')}}</th>
							</tr>
						</thead>
						<tbody>
							<tr v-if="!weekly">
								<td>
									<input type="number"
										min="-1"
										max="10"
										:disabled="weekly"
										v-model="moRule" />
								</td>
								<td>
									<input type="number"
										min="-1"
										max="10"
										:disabled="weekly"
										v-model="tuRule" />
								</td>
								<td>
									<input type="number"
										min="-1"
										max="10"
										:disabled="weekly"
										v-model="weRule" />
								</td>
								<td>
									<input type="number"
										min="-1"
										max="10"
										:disabled="weekly"
										v-model="thRule" />
								</td>
								<td>
									<input type="number"
										min="-1"
										max="10"
										:disabled="weekly"
										v-model="frRule" />
								</td>
								<td>
									<input type="number"
										min="-1"
										max="10"
										:disabled="weekly"
										v-model="saRule" />
								</td>
								<td>
									<input type="number"
										min="-1"
										max="10"
										:disabled="weekly"
										v-model="soRule" />
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Action buttons -->
			<div class="buttons-bar">
				<div class="right">
					<NcButton
						type="secondary"
						@click="close">
						{{ t('shifts','Cancel') }}
					</NcButton>
					<NcButton
						type="primary"
						@click="save">
						{{ t('shifts','Save') }}
					</NcButton>
				</div>
			</div>
		</div>
	</NcModal>
</template>

<script>
import store from '../../store'
import { mapGetters } from 'vuex'
import { showError } from '@nextcloud/dialogs'
import { NcModal, NcButton, NcMultiselect, NcTextField, NcColorPicker, NcDatetimePicker, NcCheckboxRadioSwitch } from '@nextcloud/vue'

export default {
	name: 'ShiftsTypeModal',
	components: {
		NcModal,
		NcButton,
		NcTextField,
		NcColorPicker,
		NcMultiselect,
		NcDatetimePicker,
		NcCheckboxRadioSwitch,
	},
	data() {
		return {
		}
	},
	methods: {
		save() {
			store.dispatch('saveCurrentShiftsType').then(() => {
				this.close()
			}).catch((e) => {
				console.error('Failed to save shifts type:', e)
				showError(t('shifts', 'Could not save the shiftType'))
			}).finally(() => {
				store.dispatch('triggerUnassignedShifts')
				store.dispatch('updateShifts')
			})
		},
		close() {
			this.$emit('close')
		}
	},
	computed: {
		...mapGetters({
			shiftsType: 'shiftsTypeInstance',
			skillGroups: 'getSkillGroups',
		}),

		name: {
			get() {
				return store.state.shiftsTypeInstance.name
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceName', value)
			}
		},
		color: {
			get() {
				return store.state.shiftsTypeInstance.color
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceColor', value)
			}
		},
		skillGroupId: {
			get() {
				return store.state.shiftsTypeInstance.skillGroupId
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceSkillGroupId', value)
			}
		},
		startTime: {
			get() {
				return store.state.shiftsTypeInstance.startTimestamp
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceStartTime', value)
			}
		},
		stopTime: {
			get() {
				return store.state.shiftsTypeInstance.stopTimestamp
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceStopTime', value)
			}
		},
		weekly: {
			get() {
				return store.state.shiftsTypeInstance.isWeekly
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceWeekly', value)
			}
		},
		moRule: {
			get() {
				return store.state.shiftsTypeInstance.moRule
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceMoRule', value)
			}
		},
		tuRule: {
			get() {
				return store.state.shiftsTypeInstance.tuRule
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceTuRule', value)
			}
		},
		weRule: {
			get() {
				return store.state.shiftsTypeInstance.weRule
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceWeRule', value)
			}
		},
		thRule: {
			get() {
				return store.state.shiftsTypeInstance.thRule
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceThRule', value)
			}
		},
		frRule: {
			get() {
				return store.state.shiftsTypeInstance.frRule
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceFrRule', value)
			}
		},
		saRule: {
			get() {
				return store.state.shiftsTypeInstance.saRule
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceSaRule', value)
			}
		},
		soRule: {
			get() {
				return store.state.shiftsTypeInstance.soRule
			},
			set(value) {
				store.dispatch('setShiftsTypeInstanceSoRule', value)
			}
		}
	},
}
</script>

<style lang="scss" scoped>
h3 {
	font-weight: bold;
}

.colorPreview {
	width: 100px;
	height: 40px;
	border-radius: 6px;
}

.ruleTable {
	line-height: 1.5;
	max-width: 100%;
	border-spacing: 0;
	border-color: grey;
	border-collapse: collapse;

	thead {
		tr {
			th {
				height: 48px;
				padding: 0 16px;
				font-weight: bold;
			}
			th:first-child {
				margin-right: 4px;
			}

			td {
				height: 48px;
				padding: 0 16px;
			}
		}
	}

	tbody {
		tr {
			border-bottom: 1px;

			td {
				height: 48px;
				padding: 0 16px;
			}
		}

		tr:hover {
			background: transparent;
		}
	}
}
</style>
