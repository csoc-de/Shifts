<!--
  - View to display Shifts and add Shifts or Shiftstypes if admin is current user
  -->
<template>
	<v-main class="correction_margin">
		<div v-if="isAdmin">
			<!--eslint-disable-->
			<v-menu	 v-if="!loading"
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
					<NewShift :shifts-types="shiftsTypes"
							  @cancel="closeNewShift"
							  @save="newShift">
					</NewShift>
				</v-layout>
			</v-menu>
			<v-menu	 v-if="!loading"
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
					<NewShiftType @cancel="closeNewShiftType"
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
import Calendar from '../components/Calendar'
import NewShiftType from './NewShiftType'
import NewShift from './NewShift'
import { generateUrl } from '@nextcloud/router'
import { showError, showWarning } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
	name: 'Shifts',
	components: {
		Calendar,
		NewShiftType,
		NewShift,
	},
	data() {
		return {
			currentShiftsChange: Object,
			isAdmin: false,
			loading: true,
			shiftTypeOpen: false,
			shiftOpen: false,
			shiftsChanges: [],
			analysts: [],
			shiftsTypes: [],
			shifts: [],
		}
	},
	async mounted() {
		try {
			// fetches all neccessary data
			const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			this.analysts = analystsResponse.data
			this.shiftsTypes = shiftTypeResponse.data
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = this.shiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				this.shifts.push(shift)
			})
			this.shiftsChanges = shiftsChangeResponse.data
			this.isAdmin = isAdminResponse.data
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
		// saves created Shift
		async createShift(shift) {
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
					shift.shiftsType = this.shiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
					this.shifts.push(shift)
				})
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not create the shift'))
			}
		},
		// saves created Shiftstype
		async createShiftType(shiftType) {
			try {
				await axios.post(generateUrl('/apps/shifts/shiftsType'), shiftType)
				const shiftTypesResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
				this.shiftsTypes = shiftTypesResponse.data
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not create the shiftType'))
			}
		},
	},
}
</script>
<style scoped>
#app-content > label{
	margin-left: 50px;
}

#app-content > div {
	width: 100%;
	height: 100%;
	margin-left: 50px;
	padding: 20px;
	display: flex;
	flex-direction: column;
	flex-grow: 1;
}

input[type='text'] {
	width: 100%;
}

textarea {
	flex-grow: 1;
	width: 100%;
}
</style>
