<template>
	<v-main class="correction_margin">
		<div v-if="isAdmin">
			<!--eslint-disable-->
			<v-menu
				v-model="shiftOpen"
				:close-on-content-click="false"
				:nudge-width="200"
				offset-y>
				<template v-slot:activator="{ on, attrs }">
					<v-btn
						color="light-blue"
						dark
						v-bind="attrs"
						v-on="on">
						{{ t('shifts','Neue Schicht vergeben') }}
					</v-btn>
				</template>
				<v-layout class="popover-menu-layout">
					<NewShift v-if="!loading"
						:shift-types="shiftTypes"
						@cancel="closeNewShift"
						@save="newShift">
					</NewShift>
				</v-layout>
			</v-menu>
			<v-menu
				v-model="shiftTypeOpen"
				:close-on-content-click="false"
				:nudge-width="200"
				offset-y>
				<template v-slot:activator="{ on, attrs }">
					<v-btn
						color="light-blue"
						dark
						v-bind="attrs"
						v-on="on">
						{{ t('shifts','Neuen Schichttyp anlegen') }}
					</v-btn>
				</template>
				<v-layout class="popover-menu-layout">
					<NewShiftType v-if="!loading"
						@cancel="closeNewShiftType"
						@save="newShiftType" />
				</v-layout>
			</v-menu>
		</div>
		<Calendar v-if="!loading"
				  :analysts="analysts"
				  :shifts="shifts" />
		<!-- eslint-enable-->
	</v-main>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError, showWarning } from '@nextcloud/dialogs'
import { getYYYYMMDDFromDate } from '../utils/date'
import NewShiftType from './NewShiftType'
import NewShift from './NewShift'
import Calendar from '../components/Calendar'

export default {
	name: 'ShiftsTab',
	components: {
		NewShift,
		NewShiftType,
		Calendar,
	},
	props: {
		isAdmin: Boolean,
	},
	data() {
		return {
			updating: false,
			loading: true,
			analysts: [],
			shifts: [],
			shiftTypes: [],
			shiftTypeOpen: false,
			shiftOpen: false,
			newShiftInstance: {
				analysts: [],
				shiftsType: '',
				dates: [getYYYYMMDDFromDate(new Date())],
			},
		}
	},
	async mounted() {
		try {
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			this.shiftTypes = shiftTypeResponse.data
			this.analysts = analystsResponse.data
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = this.shiftTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				this.shifts.push(shift)
			})
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts'))
		}
		this.loading = false
	},
	methods: {
		closeNewShiftType() {
			this.shiftTypeOpen = false
		},
		closeNewShift() {
			this.shiftOpen = false
		},
		async newShift(shift) {
			if (shift.analysts && shift.dates) {
				await this.createShift(shift)
				this.closeNewShift()
			} else {
				console.log('No Name for ShiftType')
				showWarning(t('shifts', 'No Analysts or Dates for Shift given'))
			}
		},
		async newShiftType(shiftType) {
			if (shiftType.name) {
				await this.createShiftType(shiftType)
			} else {
				console.log('No Name for ShiftType')
				showWarning(t('shifts', 'No Name for Shift-Type given'))
			}
			this.closeNewShiftType()
		},
		async createShift(shift) {
			this.updating = true
			try {
				await Promise.all(shift.analysts.map(async(analyst) => {
					const analystId = analyst.userId
					const shiftTypeId = shift.shiftsType.id
					const newShifts = shift.dates.map((date) => {
						return {
							analystId,
							shiftTypeId,
							date,
						}
					})
					await Promise.all(newShifts.map(async(newShift) => {
						await axios.post(generateUrl('/apps/shifts/shifts'), newShift)
					}))
				}))
				const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
				shiftResponse.data.forEach(shift => {
					shift.shiftsType = this.shiftTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
					this.shifts.push(shift)
				})
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not create the shift'))
			}
			this.updating = false
		},
		async createShiftType(shiftType) {
			this.updating = true
			try {
				await axios.post(generateUrl('/apps/shifts/shiftsType'), shiftType)
				const shiftTypesResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
				this.shiftTypes = shiftTypesResponse.data
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not create the shiftType'))
			}
			this.updating = false
		},
	},
}
</script>
