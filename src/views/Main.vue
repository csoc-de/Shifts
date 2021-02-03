<template>
	<div id="content" class="app-shifts">
		<AppContent>
			<button v-if="isAdmin && !loading"
				id="new-shift-button"
				ref="newShiftButton"
				@click="newShift">
				Neue Schicht vergeben
			</button>
			<button v-if="isAdmin && !loading"
				id="new-shift-type-button"
				ref="newShiftTypeButton"
				@click="newShiftType">
				Neuen Schichttypen anlegen
			</button>
			<Calendar />
		</AppContent>
	</div>
</template>

<script>
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import Calendar from '../components/Calendar'
export default {
	name: 'Main',
	components: {
		AppContent,
		Calendar,
	},
	data() {
		return {
			shifts: [],
			shiftTypes: [],
			currentShiftTypeId: null,
			currentShiftId: null,
			updating: false,
			loading: true,
			displayShift: true,
			isAdmin: false,
		}
	},
	computed: {
		currentShift() {
			if (this.currentShiftId === null) {
				return null
			}
			return this.shifts.find((shift) => shift.id === this.currentShiftId)
		},
		currentShiftType() {
			if (this.currentShiftTypeId === null) {
				return null
			}
			return this.shiftTypes.find((shiftType) => shiftType.id === this.currentShiftTypeId)
		},
		shiftSavePossible() {
			return this.currentShift && this.currentShift.title !== ''
		},
		shiftTypeSavePossible() {
			return this.currentShiftType && this.currentShiftType.name !== ''
		},
	},

	async mounted() {
		try {
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			const analystResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			console.warn(analystResponse.data)
			this.shifts = shiftResponse.data
			this.shiftTypes = shiftTypeResponse.data
			this.isAdmin = isAdminResponse.data
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts'))
		}
		this.loading = false
	},
	methods: {
		openShift(shift) {
			if (this.updating) {
				return
			}
			this.currentShiftId = shift.id
			this.$nextTick(() => {
				this.$refs.date.focus()
			})
		},
		openShiftType(shiftType) {
			if (this.updating) {
				return
			}
			this.currentShiftTypeId = shiftType.id
			this.$nextTick(() => {
				this.$refs.name.focus()
			})
		},
		saveShift() {
			if (this.currentShiftId === -1) {
				this.createShift(this.currentShift)
			} else {
				this.updateShift(this.currentShift)
			}
		},
		saveShiftType() {
			if (this.currentShiftTypeId === -1) {
				this.createShiftType(this.currentShiftType)
			} else {
				this.updateShiftType(this.currentShiftType)
			}
		},
		newShift() {
			if (this.currentShiftId !== -1) {
				this.currentShiftId = -1
				this.shifts.push({
					id: -1,
					userId: '',
					shiftTypeId: -1,
					date: '1.1.1970',
				})
				this.$nextTick(() => {
					this.$refs.userId.focus()
				})
			}
		},
		newShiftType() {
			if (this.currentShiftTypeId !== -1) {
				this.currentShiftTypeId = -1
				this.shiftTypes.push({
					id: -1,
					name: '',
					desc: '',
					startTimeStamp: '08:00',
					stopTimeSTamp: '12:30',
				})
				this.$nextTick(() => {
					this.$refs.name.focus()
				})
			}
		},
		cancelNewShift() {
			this.shifts.splice(this.shifts.findIndex((shift) => shift.id === -1), 1)
			this.currentShiftId = null
		},
		cancelNewShiftType() {
			this.shiftTypes.splice(this.shiftTypes.findIndex((shiftType) => shiftType.id === -1), 1)
			this.currentShiftTypeId = null
		},
		async createShift(shift) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/shifts/shifts'), shift)
				const index = this.shifts.findIndex((match) => match.id === this.currentShiftId)
				this.$set(this.shifts, index, response.data)
				this.currentShiftId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not create the shift'))
			}
			this.updating = false
		},
		async createShiftType(shiftType) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/shifts/shiftsType'), shiftType)
				const index = this.shiftTypes.findIndex((match) => match.id === this.currentShiftTypeId)
				this.$set(this.shiftTypes, index, response.data)
				this.currentShiftTypeId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not create the shiftType'))
			}
			this.updating = false
		},
		async updateShift(shift) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/shifts/shifts/${shift.id}`), shift)
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not update the shift'))
			}
			this.updating = false
		},
		async updateShiftType(shiftType) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/shifts/shiftsType/${shiftType.id}`), shiftType)
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not update the shiftType'))
			}
			this.updating = false
		},
		async deleteShift(shift) {
			try {
				await axios.delete(generateUrl(`/apps/shifts/shifts/${shift.id}`))
				this.shifts.splice(this.shifts.indexOf(shift), 1)
				if (this.currentShiftId === shift.id) {
					this.currentShiftId = null
				}
				showSuccess(t('shifts', 'Shift deleted'))
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not delete the shift'))
			}
		},
		async deleteShiftType(shiftType) {
			try {
				await axios.delete(generateUrl(`/apps/shifts/shiftsType/${shiftType.id}`))
				this.shiftTypes.splice(this.shiftTypes.indexOf(shiftType), 1)
				if (this.currentShiftTypeId === shiftType.id) {
					this.currentShiftTypeId = null
				}
				showSuccess(t('shifts', 'ShiftType deleted'))
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not delete the shiftType'))
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
