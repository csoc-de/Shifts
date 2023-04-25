<!--
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<!--
  - Modal-Dialog for creating a new ShiftsChange Request
  -->

<template>
	<NcModal
		size="normal"
		:title="t('shifts', 'New Request')"
		@close="close">

		<div
			class="container">
			<h1>{{ t('shifts', 'New Request') }}</h1>
		</div>

		<!-- Selection swap/cede -->
		<div
			v-if="!modeSwap && !modeCede"
			class="container">
			<div class="row">
				<div class="col" style="width: 50%">
					<NcButton
						type="primary"
						@click="switchToSwap">
						{{ t('shifts','Swap') }}
					</NcButton>
				</div>

				<div class="col" style="width: 50%">
					<NcButton
						type="primary"
						@click="switchToCede">
						{{ t('shifts','Cede') }}
					</NcButton>
				</div>
			</div>
		</div>

		<!-- Swap shift -->
		<div
			v-if="modeSwap"
			class="container swap">
			<div class="row">
				<div class="col">
					<span>{{swap_hint}}</span>
				</div>
			</div>

			<div class="row">
				<div class="col" style="width: 50%">
					<NcMultiselect
						v-model="swap_analyst1"
						:options="analystsList.filter((a) => { return this.isAdmin || a.uid === currentUserId})"
						track-by="email"
						label="displayName"
						:user-select="true">
						<template #singleLabel="{ option }">
							<NcListItemIcon
								v-bind="option"
								:avatar-size="24"
								:no-margin="true"
								:title="option.displayName" />
						</template>
					</NcMultiselect>
				</div>
				<div class="col" style="width: 50%">
					<NcMultiselect
						v-if="swap_analyst1 !== undefined && swap_shift1 !== undefined"
						v-model="swap_analyst2"
						:options="analystsList.filter((a) => { return this.isAdmin || a.uid !== swap_analyst1.uid /* && a.skillGroup >= swap_shift1.shiftsType.skillGroupId */ })"
						track-by="email"
						label="displayName"
						:user-select="true">
						<template #singleLabel="{ option }">
							<NcListItemIcon
								v-bind="option"
								:avatar-size="24"
								:no-margin="true"
								:title="option.displayName" />
						</template>
					</NcMultiselect>
				</div>
			</div>

			<div class="row">
				<div class="col" style="width: 50%">
					<NcDatetimePicker
						type="date"
						appendToBody
						v-model="swap_date1"
						v-if="swap_analyst1 !== undefined" />
				</div>
				<div class="col" style="width: 50%">
					<NcDatetimePicker
						type="date"
						appendToBody
						v-model="swap_date2"
						v-if="swap_analyst2 !== undefined" />
				</div>
			</div>

			<div class="row">
				<div class="col" style="width: 50%">
					<NcMultiselect
						v-if="swap_analyst1 !== undefined && swap_date1 !== undefined"
						v-model="swap_shift1"
						:options="analyst1_shifts"
						track-by="id">
						<template #option="{option}">
							<CalendarWeek v-if="option.shiftsType.isWeekly"></CalendarWeek>
							<CalendarToday v-else></CalendarToday>
							<span>{{option.shiftsType.name}}</span>
						</template>
						<template #singleLabel="{ option }">
							<CalendarWeek v-if="option.shiftsType.isWeekly"></CalendarWeek>
							<CalendarToday v-else></CalendarToday>
							<span>{{option.shiftsType.name}}</span>
						</template>
					</NcMultiselect>
				</div>
				<div class="col" style="width: 50%">
					<NcMultiselect
						v-if="swap_analyst2 !== undefined && swap_date2 !== undefined && !datesEqual(swap_date1, swap_date2)"
						v-model="swap_shift2"
						:options="analyst2_shifts"
						track-by="id">
						<template #option="{option}">
							<CalendarWeek v-if="option.shiftsType.isWeekly"></CalendarWeek>
							<CalendarToday v-else></CalendarToday>
							<span>{{option.shiftsType.name}}</span>
						</template>
						<template #singleLabel="{ option }">
							<CalendarWeek v-if="option.shiftsType.isWeekly"></CalendarWeek>
							<CalendarToday v-else></CalendarToday>
							<span>{{option.shiftsType.name}}</span>
						</template>
					</NcMultiselect>
				</div>
			</div>
		</div>

		<div
			v-if="modeCede"
			class="container cede">
			<div class="row">
				<div class="col">
					<span>{{cede_hint}}</span>
				</div>
			</div>

			<div class="row">
				<div class="col" style="width: 50%">
					<NcMultiselect
						v-model="cede_analyst1"
						:options="analystsList.filter((a) => { return this.isAdmin || a.uid === currentUserId /* && a.skillGroup >= cede_shift.shiftsType.skillGroupId */ })"
						track-by="email"
						label="displayName"
						:user-select="true">
						<template #singleLabel="{ option }">
							<NcListItemIcon
								v-bind="option"
								:avatar-size="24"
								:no-margin="true"
								:title="option.displayName" />
						</template>
					</NcMultiselect>
				</div>
				<div class="col" style="width: 50%">
					<NcMultiselect
						v-if="cede_analyst1 !== undefined && cede_shift !== undefined"
						v-model="cede_analyst2"
						:options="analystsList.filter((a) => { return this.isAdmin || a.uid !== cede_analyst1.uid /* && a.skillGroup >= cede_shift.shiftsType.skillGroupId */ })"
						track-by="email"
						label="displayName"
						:user-select="true">
						<template #singleLabel="{ option }">
							<NcListItemIcon
								v-bind="option"
								:avatar-size="24"
								:no-margin="true"
								:title="option.displayName" />
						</template>
					</NcMultiselect>
				</div>
			</div>

			<div class="row">
				<div class="col" style="width: 50%">
					<NcDatetimePicker
						type="date"
						appendToBody
						v-model="cede_date"
						v-if="cede_analyst1 !== undefined" />
				</div>
			</div>

			<div class="row">
				<div class="col" style="width: 50%">
					<NcMultiselect
						v-if="cede_analyst1 !== undefined && cede_date !== undefined"
						v-model="cede_shift"
						:options="analyst1_shifts"
						track-by="id">
						<template #option="{option}">
							<CalendarWeek v-if="option.shiftsType.isWeekly"></CalendarWeek>
							<CalendarToday v-else></CalendarToday>
							<span>{{option.shiftsType.name}}</span>
						</template>
						<template #singleLabel="{ option }">
							<CalendarWeek v-if="option.shiftsType.isWeekly"></CalendarWeek>
							<CalendarToday v-else></CalendarToday>
							<span>{{option.shiftsType.name}}</span>
						</template>
					</NcMultiselect>
				</div>
			</div>
		</div>

		<!-- Action buttons -->
		<div class="container">
			<div class="buttons-bar">
				<div class="right">
					<NcButton
						type="secondary"
						@click="close">
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
	</NcModal>
</template>

<script>
import dayjs from 'dayjs'
import store from '../../store'
import { mapGetters } from 'vuex'
import { showError } from '@nextcloud/dialogs'
import { getCurrentUser } from '@nextcloud/auth'
import CalendarWeek from 'vue-material-design-icons/CalendarWeek'
import CalendarToday from 'vue-material-design-icons/CalendarToday'
import { NcButton, NcModal, NcMultiselect, NcListItemIcon, NcDatetimePicker } from '@nextcloud/vue'

export default {
	name: 'ChangeRequestModal',
	components: {
		NcModal,
		NcButton,
		CalendarWeek,
		CalendarToday,
		NcMultiselect,
		NcListItemIcon,
		NcDatetimePicker,
	},
	props: {
		isAdmin: {
			type: Boolean,
		},
		shifts: {
			type: Array,
		},
		analysts: {
			type: Array,
		},
	},
	data() {
		return {
			selectedNewAnalyst: null,
			selectedOldAnalyst: null,
			oldAnalystSelectedShifts: [],
			newAnalystSelectedShifts: [],
			mutableAnalysts: this.analysts,
			tab: t('shifts', 'Swap'),
			desc: '',
			tabItems: [t('shifts', 'Swap'), t('shifts', 'Cede')],

			currentUserId: -1,

			modeSwap: false,
			modeCede: false,
			analystsList: [],
			analyst1_shifts: [],
			analyst2_shifts: [],

			savable: false,

			// Swap
			swap_analyst1: undefined,
			swap_analyst2: undefined,
			swap_shift1: undefined,
			swap_shift2: undefined,
			swap_date1: undefined,
			swap_date2: undefined,
			swap_desc: '',
			swap_hint: '',

			// Cede
			cede_analyst1: undefined,
			cede_analyst2: undefined,
			cede_shift: undefined,
			cede_date: undefined,
			cede_desc: '',
			cede_hint: '',
		}
	},
	methods: {
		switchToSwap() {
			this.modeSwap = true
			this.updateSwapHint()
			// this.swap_hint = t('shifts', 'Please select the first user to swap a shift with.')
		},
		switchToCede() {
			this.modeCede = true
			this.updateCedeHint()
		},
		close() {
			this.$emit('close')
		},

		/*
			Swap shifts functions
		 */
		updateSwapHint() {
			this.savable = false
			if (this.swap_analyst1 === undefined) {
				this.swap_hint = t('shifts', 'Please select the first user you want to swap shifts with')
			} else if (this.swap_date1 === undefined) {
				this.swap_hint = t('shifts', 'Please select the date of the first shift you want to swap')
			} else if (this.swap_shift1 === undefined) {
				this.swap_hint = t('shifts', 'Please select the shift of the first user you want to swap')
			} else if (this.swap_analyst2 === undefined) {
				this.swap_hint = t('shifts', 'Please select the second user you want to swap shifts with')
			} else if (this.swap_date2 === undefined) {
				this.swap_hint = t('shifts', 'Please select the date of the second shift you want to swap')
			} else if (this.datesEqual(this.swap_date1, this.swap_date2)) {
				this.swap_hint = t('shifts', 'Please select a different date for the second shift you want to swap')
			} else if (this.swap_shift2 === undefined) {
				this.swap_hint = t('shifts', 'Please select the shift of the second user you want to swap')
			} else {
				this.swap_hint = ''
				this.savable = true
			}
		},

		updateSwapShifts() {
			if (this.swap_analyst1 !== undefined && this.swap_date1 !== undefined) {
				const date = dayjs(this.swap_date1)
				this.analyst1_shifts = this.getAllDailyShiftsForAnalyst(this.swap_analyst1.uid, date.format('YYYY-MM-DD'))
				if (this.analyst1_shifts === undefined) {
					this.analyst1_shifts = []
				}

				const analystShifts = this.getAllWeeklyShiftsForAnalyst(this.swap_analyst1.uid, date)
				if (analystShifts !== undefined) {
					this.analyst1_shifts = this.analyst1_shifts.concat(analystShifts)
				}
			}

			if (this.swap_analyst2 !== undefined && this.swap_date2 !== undefined) {
				const date = dayjs(this.swap_date2)
				this.analyst2_shifts = this.getAllDailyShiftsForAnalyst(this.swap_analyst2.uid, date.format('YYYY-MM-DD'))
				if (this.analyst2_shifts === undefined) {
					this.analyst2_shifts = []
				}

				const analystShifts = this.getAllWeeklyShiftsForAnalyst(this.swap_analyst2.uid, date)
				if (analystShifts !== undefined) {
					this.analyst2_shifts = this.analyst2_shifts.concat(analystShifts)
				}

				// Filter out all shifts with a higher level than analyst 1
				this.analyst2_shifts = this.analyst2_shifts.filter((shift) => {
					return this.swap_analyst1.skillGroup >= shift.shiftsType.skillGroupId
				})

				// Filter out all shifts that have not the same type (daily/weekly)
				this.analyst2_shifts = this.analyst2_shifts.filter((shift) => {
					return this.swap_shift1.shiftsType.isWeekly === shift.shiftsType.isWeekly
				})

				// Filter out all shifts that have not the same type (shiftType)
				if (this.getShiftChangeSameType) {
					this.analyst2_shifts = this.analyst2_shifts.filter((shift) => {
						return this.swap_shift1.shiftTypeId === shift.shiftTypeId
					})
				}
			}
		},

		/*
			Cede shift functions
		 */
		updateCedeHint() {
			this.savable = false
			if (this.cede_analyst1 === undefined) {
				this.cede_hint = t('shifts', 'Please select the first user you want to cede a shift to')
			} else if (this.cede_date === undefined) {
				this.cede_hint = t('shifts', 'Please select the date of your shift you want to cede')
			} else if (this.cede_shift === undefined) {
				this.cede_hint = t('shifts', 'Please select your shift you want to cede')
			} else if (this.cede_analyst2 === undefined) {
				this.cede_hint = t('shifts', 'Please select the user you want to cede your shift to')
			} else if (this.analyst2_shifts.length > 0) {
				this.cede_hint = t('shifts', 'This user has already the same kind of shift on that day. Please select another user you want to cede to.')
			} else {
				this.cede_hint = ''
				this.savable = true
			}
		},

		updateCedeShifts() {
			if (this.cede_analyst1 !== undefined && this.cede_date !== undefined) {
				const date = dayjs(this.cede_date)
				this.analyst1_shifts = this.getAllDailyShiftsForAnalyst(this.cede_analyst1.uid, date.format('YYYY-MM-DD'))
				if (this.analyst1_shifts === undefined) {
					this.analyst1_shifts = []
				}

				const analystShifts = this.getAllWeeklyShiftsForAnalyst(this.cede_analyst1.uid, date)
				if (analystShifts !== undefined) {
					this.analyst1_shifts = this.analyst1_shifts.concat(analystShifts)
				}
			}

			if (this.cede_analyst2 !== undefined && this.cede_date !== undefined) {
				const date = dayjs(this.cede_date)
				this.analyst2_shifts = this.getAllDailyShiftsForAnalyst(this.cede_analyst2.uid, date.format('YYYY-MM-DD'))
				if (this.analyst2_shifts === undefined) {
					this.analyst2_shifts = []
				}

				const analystShifts = this.getAllWeeklyShiftsForAnalyst(this.cede_analyst2.uid, date)
				if (analystShifts !== undefined) {
					this.analyst2_shifts = this.analyst2_shifts.concat(analystShifts)
				}

				// Filter out all shifts with a higher level than analyst 1
				this.analyst2_shifts = this.analyst2_shifts.filter((shift) => {
					return this.cede_analyst1.skillGroup >= shift.shiftsType.skillGroupId
				})

				// Filter out all shifts that have not the same type (daily/weekly)
				this.analyst2_shifts = this.analyst2_shifts.filter((shift) => {
					return this.cede_shift.shiftsType.isWeekly === shift.shiftsType.isWeekly
				})

				// Filter out all shifts that have not the same type (shiftType)
				if (this.getShiftChangeSameType) {
					this.analyst2_shifts = this.analyst2_shifts.filter((shift) => {
						return this.cede_shift.shiftTypeId === shift.shiftTypeId
					})
				}
			}
		},

		/*
			Old functions
		 */
		datesEqual(date1, date2) {
			return dayjs(date1).format('YYYY-MM-DD') === dayjs(date2).format('YYYY-MM-DD')
		},

		removeOldAnalyst() {
			this.selectedOldAnalyst = null
		},
		removeNewAnalyst() {
			this.selectedNewAnalyst = null
		},
		removeOldAnalystShift(item) {
			const index = this.oldAnalystSelectedShifts.indexOf(item.id)
			if (index >= 0) this.oldAnalystSelectedShifts.splice(index, 1)
		},
		removeNewAnalystShift(item) {
			const index = this.newAnalystSelectedShifts.indexOf(item.id)
			if (index >= 0) this.newAnalystSelectedShifts.splice(index, 1)
		},
		// function for filtering the input of the analyst autocompletion fields
		analystFilter(item, queryText, itemText) {
			return (
				item.name.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
				|| item.email.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
			)
		},
		// function for filtering the input of the shifts autocompletion fields
		shiftsFilter(item, queryText, itemText) {
			return (
				item.shiftsType.name.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
				|| item.date.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
			)
		},

		// saves the new request
		save() {
			if (this.modeSwap) {
				this.saveSwap()
			} else if (this.modeCede) {
				this.saveCede()
			}
		},
		saveSwap() {
			store.dispatch('requestShiftChange', {
				oldAnalystId: this.swap_analyst1.uid,
				newAnalystId: this.swap_analyst2.uid,
				oldShiftsId: this.swap_shift1.id,
				newShiftsId: this.swap_shift2.id,
				desc: this.swap_desc,
				type: 0,
				adminApproval: '0',
				adminApprovalDate: '',
				analystApproval: '0',
				analystApprovalDate: '',
			}).then(() => {
				store.dispatch('fetchShiftsChanges')
				this.close()
			}).catch((e) => {
				console.error('Failed to request for shift change:', e)
				showError(t('shifts', 'Failed to request for shift change.'))
			}).finally(() => {
				store.dispatch('updateShifts')
			})
		},
		saveCede() {
			store.dispatch('requestShiftChange', {
				oldAnalystId: this.cede_analyst1.uid,
				newAnalystId: this.cede_analyst2.uid,
				oldShiftsId: this.cede_shift.id,
				newShiftsId: -1,
				desc: this.cede_desc,
				type: 1,
				adminApproval: '0',
				adminApprovalDate: '',
				analystApproval: '0',
				analystApprovalDate: '',
			}).then(() => {
				store.dispatch('fetchShiftsChanges')
				this.close()
			}).catch((e) => {
				console.error('Failed to request for shift change:', e)
				showError(t('shifts', 'Failed to request for shift change.'))
			}).finally(() => {
				store.dispatch('updateShifts')
			})
		}
	},
	computed: {
		...mapGetters({
			allAnalysts: 'allAnalysts',
			getShiftsForAnalyst: 'getShiftsForAnalyst',
			getShiftChangeSameType: 'getShiftChangeSameType',
			getAllDailyShiftsForAnalyst: 'getAllDailyShiftsForAnalyst',
			getAllWeeklyShiftsForAnalyst: 'getAllWeeklyShiftsForAnalyst',
		}),
		// returns list of shifts from selected old analyst
		oldAnalystShifts() {
			const shiftsTypeId = Math.max(...this.newAnalystSelectedShifts.map((shiftId) => {
				const shift = this.$store.getters.getShiftById(shiftId.toString())
				return parseInt(shift.shiftsType.id)
			}))
			return this.shifts.filter((shift) => {
				return (shift.userId === this.selectedOldAnalyst.uid)
					&& (this.selectedNewAnalyst ? this.selectedNewAnalyst.skillGroup >= shift.shiftsType.skillGroupId : true)
					&& (isFinite(shiftsTypeId) ? shift.shiftsType.id === shiftsTypeId : true)
			})
		},
		// return list of shifts from selected new analyst
		newAnalystShifts() {
			const shiftsTypeId = Math.max(...this.oldAnalystSelectedShifts.map((shiftId) => {
				const shift = this.$store.getters.getShiftById(shiftId.toString())
				return parseInt(shift.shiftsType.id)
			}))
			return this.shifts.filter((shift) => {
				return (shift.userId === this.selectedNewAnalyst.uid)
					&& (this.selectedOldAnalyst ? this.selectedOldAnalyst.skillGroup >= shift.shiftsType.skillGroupId : true)
					&& (isFinite(shiftsTypeId) ? shift.shiftsType.id === shiftsTypeId : true)
			})
		},
		// returns list of analysts excluding already selected ones
		excludeOldAnalystSelected() {
			if (this.selectedOldAnalyst) {
				return this.mutableAnalysts.filter((analyst) => {
					const reqSkillLevel = Math.max(...this.oldAnalystSelectedShifts.map((shiftId) => {
						const shift = this.$store.getters.getShiftById(shiftId.toString())
						return parseInt(shift.shiftsType.skillGroupId)
					}))
					return analyst.uid !== this.selectedOldAnalyst.uid && analyst.skillGroup >= reqSkillLevel
				})
			} else {
				return this.mutableAnalysts
			}
		},
		// returns list of analysts excluding already selected ones
		excludeNewAnalystSelected() {
			if (this.selectedNewAnalyst) {
				return this.mutableAnalysts.filter((analyst) => {
					const reqSkillLevel = Math.max(...this.newAnalystSelectedShifts.map((shiftId) => {
						const shift = this.$store.getters.getShiftById(shiftId.toString())
						return parseInt(shift.shiftsType.skillGroupId)
					}))
					return analyst.uid !== this.selectedNewAnalyst.uid && analyst.skillGroup >= reqSkillLevel
				})
			} else {
				return this.mutableAnalysts
			}
		},
		// returns whether the Save button is disabled or not
		saveDisabled() {
			const shiftsOk = (this.tab === t('shifts', 'Swap') && this.newAnalystSelectedShifts.length === this.oldAnalystSelectedShifts.length)
				|| (this.tab === t('shifts', 'Cede') && this.newAnalystSelectedShifts.length > 0)
			const analystsOk = this.selectedOldAnalyst && this.selectedNewAnalyst

			return shiftsOk && analystsOk
		},
	},
	mounted() {
	},
	beforeMount() {
		this.analystsList = this.allAnalysts.map(analyst => {
			let displayName
			if (analyst.name && analyst.email === undefined) {
				displayName = analyst.name
			} else if (analyst.name && analyst.email) {
				displayName = `${analyst.name} (${analyst.email})`
			} else {
				displayName = analyst.email
			}

			return {
				uid: analyst.uid,
				name: analyst.name,
				email: analyst.email,
				skillGroup: analyst.skillGroup,
				displayName
			}
		})

		this.currentUserId = getCurrentUser().uid

		if (!this.isAdmin) {
			this.swap_analyst1 = this.analystsList.find((analyst) => {
				return (analyst.uid === this.currentUserId)
			})
			this.cede_analyst1 = this.swap_analyst1
		}
	},
	watch: {
		swap_analyst1: {
			handler() {
				this.updateSwapHint()
				this.updateSwapShifts()
			}
		},
		swap_date1: {
			handler() {
				this.updateSwapHint()
				this.updateSwapShifts()
			}
		},
		swap_shift1: {
			handler() {
				this.updateSwapHint()
				this.updateSwapShifts()
				this.swap_shift2 = undefined
			}
		},
		swap_analyst2: {
			handler() {
				this.updateSwapHint()
				this.updateSwapShifts()
			}
		},
		swap_date2: {
			handler() {
				this.updateSwapHint()
				this.updateSwapShifts()
			}
		},
		swap_shift2: {
			handler() {
				this.updateSwapHint()
				this.updateSwapShifts()
			}
		},

		cede_analyst1: {
			handler() {
				this.updateCedeShifts()
				this.updateCedeHint()
			}
		},
		cede_analyst2: {
			handler() {
				this.updateCedeShifts()
				this.updateCedeHint()
			}
		},
		cede_shift: {
			handler() {
				this.updateCedeShifts()
				this.updateCedeHint()
			}
		},
		cede_date: {
			handler() {
				this.updateCedeShifts()
				this.updateCedeHint()
			}
		}
	}
}
</script>

<style scoped lang="scss">
.container.swap {
	width: 100%;
	height: 100%;

	min-height: 300px;
}

.container.cede {
	width: 100%;
	height: 100%;

	min-height: 300px;
}
</style>
