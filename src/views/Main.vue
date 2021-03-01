<template>
	<div id="content" class="app-shifts">
		<AppContent>
			<v-popover ref="shiftPopover"
				:open="shiftOpen">
				<button v-if="isAdmin && !loading"
					id="new-shift-button"
					ref="newShiftButton"
					@click="openNewShift">
					Neue Schicht vergeben
				</button>
				<NewShift v-if="!loading"
					slot="popover"
					:shift-types="shiftTypes"
					@cancel="closeNewShift"
					@save="newShift" />
			</v-popover>
			<v-popover ref="shiftTypePopover"
				:open="shiftTypeOpen">
				<button v-if="isAdmin && !loading"
					id="new-shift-type-button"
					ref="newShiftTypeButton"
					@click="openNewShiftType">
					Neuen Schichttypen anlegen
				</button>
				<NewShiftType v-if="!loading"
					slot="popover"
					@cancel="closeNewShiftType"
					@save="newShiftType" />
			</v-popover>
			<Calendar v-if="!loading"
				:analysts="analysts"
				:shifts="shifts" />
		</AppContent>
	</div>
</template>
<script>
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import Calendar from '../components/Calendar'
import NewShiftType from './NewShiftType'
import NewShift from './NewShift'
import { getYYYYMMDDFromDate } from '../utils/date'

export default {
	name: 'Main',
	components: {
		NewShift,
		NewShiftType,
		AppContent,
		Calendar,
	},
	data() {
		return {
			shifts: [],
			shiftTypes: [],
			updating: false,
			loading: true,
			displayShift: true,
			isAdmin: false,
			shiftTypeOpen: false,
			shiftOpen: false,
			analysts: [],
		}
	},
	async mounted() {
		try {
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			this.shiftTypes = shiftTypeResponse.data
			this.isAdmin = isAdminResponse.data
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
		openNewShift() {
			this.shiftOpen = true
		},
		openNewShiftType() {
			this.shiftTypeOpen = true
		},
		async newShift(shift) {
			await this.createShift(shift)
			this.closeNewShift()
		},
		async newShiftType(shiftType) {
			await this.createShiftType(shiftType)
			this.closeNewShiftType()
		},
		async createShift(shift) {
			this.updating = true
			try {
				await Promise.all(shift.analysts.map(async(analyst) => {
					let date = shift.date
					if (shift.date.date) {
						date = shift.date.date
					}
					const analystId = analyst.userId
					const shiftTypeId = shift.shiftsType.id
					const newShift = {
						analystId,
						shiftTypeId,
						date: getYYYYMMDDFromDate(date),
					}
					await axios.post(generateUrl('/apps/shifts/shifts'), newShift)
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
