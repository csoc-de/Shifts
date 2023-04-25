<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<!--
  - Main Component of Vue-App
  -->
<template>
	<div v-if="getSettingsFetched">
		<!-- General settings -->
		<NcSettingsSection
			:title="t('shifts','General')"
			doc-url="https://github.com/csoc-de/Shifts"
			:limit-width="true">
			<div class="container">
				<div class="row">
					<div class="col">
						<NcTextField :value.sync="shiftCalendarName"
							:placeholder="t('shifts','Name of the Shiftscalendar')"
							trailing-button-icon="close"
							:show-trailing-button="shiftCalendarName !== ''"
							@trailing-button-click="clearShiftCalendarName">
							<Magnify :size="16" />
						</NcTextField>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<NcTextField :value.sync="analystGroup"
							:placeholder="t('shifts', 'Name of the Analyst Group')"
							trailing-button-icon="close"
							:show-trailing-button="analystGroup !== ''"
							@trailing-button-click="clearAnalystGroupName">
							<Magnify :size="16" />
						</NcTextField>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<NcTextField :value.sync="shiftAdminGroup"
							:placeholder="t('shifts', 'Name of the Shiftsadmin Group')"
							trailing-button-icon="close"
							:show-trailing-button="shiftAdminGroup !== ''"
							@trailing-button-click="clearShiftAdminGroupName">
							<Magnify :size="16" />
						</NcTextField>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<span class="timezone">{{t('shifts', 'Timezone:')}}</span>
					</div>
					<div class="col">
						<NcTimezonePicker v-model="shiftTimezone" />
					</div>
				</div>

				<div class="row">
					<div class="col">
						<NcCheckboxRadioSwitch :checked.sync="shiftChangeSameShiftType">{{t('shifts', 'User can only swap shifts of the same type')}}</NcCheckboxRadioSwitch>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<NcCheckboxRadioSwitch :checked.sync="shiftAddUserCalendarEvent">{{t('shifts', 'Add calendar events to the shift users calendar')}}</NcCheckboxRadioSwitch>
					</div>
				</div>
			</div>
		</NcSettingsSection>

		<!-- Organizer -->
		<NcSettingsSection
			:title="t('shifts','Organizer')"
			:limit-width="true">
			<div class="container">
				<div class="row">
					<div class="col">
						<NcTextField :value.sync="shiftOrganizerName"
							:placeholder="t('shifts', 'Name of the Shiftsorganizer')"
							trailing-button-icon="close"
							:show-trailing-button="shiftOrganizerName !== ''"
							@trailing-button-click="clearShiftOrganizerName">
							<Magnify :size="16" />
						</NcTextField>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<NcTextField :value.sync="shiftOrganizerEmail"
							:placeholder="t('shifts', 'Email of the Shiftsorganizer')"
							trailing-button-icon="close"
							:show-trailing-button="shiftOrganizerEmail !== ''"
							@trailing-button-click="clearShiftOrganizerEmail">
							<Magnify :size="16" />
						</NcTextField>
					</div>
				</div>
			</div>
		</NcSettingsSection>

		<!-- Skill level -->
		<NcSettingsSection
			:title="t('shifts','Skill level')"
			:limit-width="true">
			<div class="container">
				<div class="row">
					<table class="skillTable">
						<thead>
							<tr>
								<th>ID</th>
								<th>{{t('shifts', 'Name')}}</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(group, i) in skillGroups"
								:key="i">
								<td>
									{{group.id}}
								</td>
								<td>
									<NcTextField :value.sync="group.name"
										:placeholder="t('shifts', 'Name of the Analyst-Skill Group')"
										trailing-button-icon="close"
										:show-trailing-button="group.name !== ''"
										@trailing-button-click="group.name = ''"
										@update:value="savable=true">
										<Magnify :size="16" />
									</NcTextField>
								</td>
								<td style="padding-left: 10px">
									<NcButton
										@click="removeSkillGroup(group)"
										type="error">
										<template #icon>
											<Delete :size="16" />
										</template>
									</NcButton>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="row" style="padding-top: 10px">
					<NcButton
						type="secondary"
						@click="appendEmptySkillGroup">
						{{ t('shifts','Add') }}
					</NcButton>
				</div>
			</div>
		</NcSettingsSection>

		<!-- Action buttons -->
		<div class="container">
			<div class="buttons-bar">
				<div class="left">
					<NcButton
						type="secondary"
						@click="cancel">
						{{ t('shifts','Cancel') }}
					</NcButton>
					<NcButton
						type="primary"
						@click="save"
						:disabled="!savable">
						{{ t('shifts','Save') }}
					</NcButton>
				</div>
			</div>
		</div>
	</div>
	<div v-else>
		<span>Loading...</span>
	</div>
</template>

<script>
import store from './store'
import { mapGetters } from 'vuex'
import { NcButton, NcTextField, NcCheckboxRadioSwitch } from '@nextcloud/vue'
import Delete from 'vue-material-design-icons/Delete'
import Magnify from 'vue-material-design-icons/Magnify'
import NcTimezonePicker from '@nextcloud/vue/dist/Components/NcTimezonePicker'
import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'Settings',
	components: {
		Delete,
		NcButton,
		Magnify,
		NcTextField,
		NcTimezonePicker,
		NcSettingsSection,
		NcCheckboxRadioSwitch,
	},
	data() {
		return {
			savable: false,
			tz: '',
		}
	},
	methods: {
		clearShiftCalendarName() {
			this.shiftCalendarName = ''
		},
		clearAnalystGroupName() {
			this.analystGroup = ''
		},
		clearShiftAdminGroupName() {
			this.shiftAdminGroup = ''
		},
		clearShiftOrganizerName() {
			this.shiftOrganizerName = ''
		},
		clearShiftOrganizerEmail() {
			this.shiftOrganizerEmail = ''
		},

		appendEmptySkillGroup() {
			store.dispatch('addEmptyRowToSkillGroups')
		},
		removeSkillGroup(group) {
			this.savable = true
			store.dispatch('removeSkillGroup', group)
		},

		fetchSettings() {
			store.dispatch('fetchSettings').then(() => {
				console.debug('Settings successfully loaded.')
			}).catch((e) => {
				console.error(e)
				showError(t('shifts', 'Could not fetch Settings'))
			})
			this.savable = false
		},

		cancel() {
			this.fetchSettings()
		},
		save() {
			store.dispatch('saveSettings').then(() => {
				console.debug('Settings successfully saved')
				this.savable = false
			}).catch((e) => {
				console.error(e)
				showError(t('shifts', 'Could not save Settings'))
			})
		}
	},
	computed: {
		...mapGetters({
			getCalendarName: 'getCalendarName',
			getAddUserCalendarEvent: 'getAddUserCalendarEvent',
			getAnalystGroup: 'getAnalystGroup',
			getShiftAdminGroup: 'getShiftAdminGroup',

			getOrganizerName: 'getOrganizerName',
			getOrganizerEmail: 'getOrganizerEmail',
			getShiftTimezone: 'getShiftTimezone',

			getShiftChangeSameType: 'getShiftChangeSameType',

			getSkillGroups: 'getSkillGroups',

			getSettingsFetched: 'getSettingsFetched',
		}),

		shiftCalendarName: {
			get() {
				return this.getCalendarName
			},
			set(value) {
				this.savable = true
				store.dispatch('updateCalendarName', value)
			}
		},
		shiftAddUserCalendarEvent: {
			get() {
				return this.getAddUserCalendarEvent
			},
			set(value) {
				this.savable = true
				store.dispatch('updateAddUserCalendarEvent', value)
			}
		},
		analystGroup: {
			get() {
				return this.getAnalystGroup
			},
			set(value) {
				this.savable = true
				store.dispatch('updateAnalystGroup', value)
			}
		},
		shiftAdminGroup: {
			get() {
				return this.getShiftAdminGroup
			},
			set(value) {
				this.savable = true
				store.dispatch('updateShiftAdminGroup', value)
			}
		},

		shiftOrganizerName: {
			get() {
				return this.getOrganizerName
			},
			set(value) {
				this.savable = true
				store.dispatch('updateOrganizerName', value)
			}
		},
		shiftOrganizerEmail: {
			get() {
				return this.getOrganizerEmail
			},
			set(value) {
				this.savable = true
				store.dispatch('updateOrganizerEmail', value)
			}
		},
		shiftTimezone: {
			get() {
				return this.getShiftTimezone
			},
			set(value) {
				this.savable = true
				store.dispatch('updateShiftTimezone', value)
			}
		},

		shiftChangeSameShiftType: {
			get() {
				return this.getShiftChangeSameType
			},
			set(value) {
				this.savable = true
				store.dispatch('updateShiftChangeSameType', value)
			}
		},

		skillGroups: {
			get() {
				return this.getSkillGroups
			},
			set(value) {
				this.savable = true
			}
		}
	},
	created() {
		this.fetchSettings()
	}
}
</script>

<style lang="scss">
@import '../css/custom';

.timezone {
	height: 100%;
	display: flex;
	align-items: center;

	padding-left: 6px;
}

.container {
	.skillTable {
		margin-left: 16px;

		thead {
			th {
				padding-right: 12px
			}
		}

		tbody {
			tr {
				td {
					padding-top: 10px;
				}
			}
			tr:hover {
				background-color: transparent;
			}
		}
	}

	.buttons-bar {
		margin-left: 4px;
		margin-top: 8px;
	}
}
</style>
