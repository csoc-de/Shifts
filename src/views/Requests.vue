<template>
	<div class="requests_content">
		<v-btn
			v-if="isAnalyst || isAdmin"
			color="light-blue"
			@click="openDialog()">
			{{ t('shifts','Neue Anfrage') }}
		</v-btn>
		<RequestsModal v-if="dialogOpen"
			:is-admin="isAdmin"
			:shifts="shifts"
			:analysts="analysts"
			@close="closeDialog"
			@saved="dialogSaved" />
		<h1>{{ t('shifts','In Bearbeitung') }}</h1>
		<!--eslint-disable-->
		<v-list v-if="!loading">
			<v-list-group
				v-for="shiftsChange in inProgressShiftsChanges"
				:key="shiftsChange.id"
				v-model="shiftsChange.active"
				:prepend-icon=" shiftsChange.type === '0' ? 'mdi-swap-horizontal' : 'icon-confirm'">
				<template v-slot:activator>
					<v-list-item-content>
						<v-list-item-title v-text="shiftsChange.type === '0' ? t('shifts','Tausch') : t('shifts','Angebot')"></v-list-item-title>
						<v-row>
							<v-col
								cols="12"
								em="6"
								class="display_name_col">
								<v-list-item-subtitle v-text="getDisplayNameByUserId(shiftsChange.oldAnalystId)"></v-list-item-subtitle>
							</v-col>
							<v-col
								cols="12"
								em="6"
								class="shifts_details_col">
								<v-list-item-subtitle v-text="getShiftsDetailsByShiftsIdAsString(shiftsChange.oldShiftsId)"></v-list-item-subtitle>
							</v-col>
						</v-row>
						<v-row>
							<v-col
								cols="12"
								em="6"
								class="display_name_col">
								<v-list-item-subtitle v-text="getDisplayNameByUserId(shiftsChange.newAnalystId)"></v-list-item-subtitle>
							</v-col>
							<v-col
								cols="12"
								em="6"
								class="shifts_details_col">
								<v-list-item-subtitle v-text="getShiftsDetailsByShiftsIdAsString(shiftsChange.newShiftsId)"></v-list-item-subtitle>
							</v-col>
						</v-row>
					</v-list-item-content>
				</template>
				<div class="float_right list_items">
					<p v-if="isAdmin && shiftsChange.analystApproval === '1'">
						{{ t('shifts', 'Analyst Genehmigung: ')
					+ shiftsChange.analystApproval + t('shifts', ' am ')
					+ getDateString(shiftsChange.analystApprovalDate) }}
					</p>
					<v-btn
						v-if="(shiftsChange.newAnalystId === currentUser && shiftsChange.analystApproval !== '1') || (isAdmin && shiftsChange.analystApproval === '1')"
						color="red"
						@click="disapproved(shiftsChange)">
						{{ t('shifts','Ablehnen') }}
						<v-icon>
							icon-close
						</v-icon>
					</v-btn>
					<v-btn
						v-if="(shiftsChange.newAnalystId === currentUser && shiftsChange.analystApproval !== '1') || (isAdmin && shiftsChange.analystApproval === '1')"
						color="green"
						@click="approved(shiftsChange)">
						{{ t('shifts','Genehmigen') }}
						<v-icon>
							icon-checkmark
						</v-icon>
					</v-btn>
				</div>
			</v-list-group>
		</v-list>
		<h1>{{ t('shifts','Bearbeitet') }}</h1>
		<v-list>
			<v-list-group
				v-for="shiftsChange in doneShiftsChanges"
				:key="shiftsChange.id"
				v-model="shiftsChange.active"
				:prepend-icon=" shiftsChange.type === '0' ? 'mdi-swap-horizontal' : 'icon-confirm'">
				<template v-slot:activator>
					<v-list-item-content>
						<v-list-item-title v-text="shiftsChange.type === '0' ? t('shifts','Tausch') : t('shifts','Angebot')"></v-list-item-title>
						<v-row>
							<v-col
								cols="12"
								em="6"
								class="display_name_col">
								<v-list-item-subtitle v-text="getDisplayNameByUserId(shiftsChange.oldAnalystId)"></v-list-item-subtitle>
							</v-col>
							<v-col
								cols="12"
								em="6"
								class="shifts_details_col">
								<v-list-item-subtitle v-text="getShiftsDetailsByShiftsIdAsString(shiftsChange.oldShiftsId)"></v-list-item-subtitle>
							</v-col>
						</v-row>
						<v-row>
							<v-col
								cols="12"
								em="6"
								class="display_name_col">
								<v-list-item-subtitle v-text="getDisplayNameByUserId(shiftsChange.newAnalystId)"></v-list-item-subtitle>
							</v-col>
							<v-col
								cols="12"
								em="6"
								class="shifts_details_col">
								<v-list-item-subtitle v-text="getShiftsDetailsByShiftsIdAsString(shiftsChange.newShiftsId)"></v-list-item-subtitle>
							</v-col>
						</v-row>
					</v-list-item-content>
				</template>
				<div class="done_shifts_items list_items">
					<p>
						{{ t('shifts', 'Analyst Genehmigung: ')
					+ shiftsChange.analystApproval + t('shifts', ' am ')
					+ getDateString(shiftsChange.analystApprovalDate) }}
					</p>
					<p>
						{{ t('shifts', 'Admin Genehmigung: ')
					+ shiftsChange.adminApproval + t('shifts', ' am ')
					+ getDateString(shiftsChange.adminApprovalDate) }}
					</p>
				</div>
			</v-list-group>
		</v-list>
		<!--eslint-enable-->
	</div>
</template>

<script>
import RequestsModal from '../components/Modal/RequestsModal'
import { showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
export default {
	name: 'Requests',
	components: {
		RequestsModal,
	},
	data() {
		return {
			loading: true,
			dialogOpen: false,
			isAdmin: false,
			shiftsChanges: [],
			analysts: [],
			shiftsTypes: [],
			shifts: [],
			currentUser: '',
		}
	},
	computed: {
		inProgressShiftsChanges() {
			return this.shiftsChanges.filter((shiftsChange) => {
				return !(shiftsChange.adminApprovalDate !== '' && shiftsChange.analystApprovalDate !== '')
			})
		},
		doneShiftsChanges() {
			return this.shiftsChanges.filter((shiftsChange) => {
				return shiftsChange.adminApprovalDate !== '' && shiftsChange.analystApprovalDate !== ''
			})
		},
		isAnalyst() {
			let found = false
			for (let i = 0; i< this.analysts.length; i++) {
				if (this.analysts[i].uid === this.currentUser) {
					found = true
					break
				}
			}
			return found
		},
	},
	async mounted() {
		try {
			let shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange/getAllByUserId'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			const currentUserResponse = await axios.get(generateUrl('/apps/shifts/getCurrentUserId'))
			this.currentUser = currentUserResponse.data
			this.isAdmin = isAdminResponse.data
			if (this.isAdmin) {
				shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			}
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
		openDialog() {
			this.dialogOpen = true
		},
		closeDialog() {
			this.dialogOpen = false
		},
		dialogSaved(shiftsChanges) {
			this.dialogOpen = false
			this.shiftsChanges.push(...shiftsChanges)
		},
		getDisplayNameByUserId(userId) {
			return this.analysts.find((analyst) => {
				return analyst.uid === userId
			}).name
		},
		async disapproved(shiftsChange) {
			if (this.isAdmin) {
				shiftsChange.adminApproval = false
				shiftsChange.adminApprovalDate = new Date()
			} else {
				shiftsChange.analystApproval = false
				shiftsChange.analystApprovalDate = new Date()
			}
			await this.saveShiftsChange(shiftsChange)
		},
		async approved(shiftsChange) {
			if (this.isAdmin) {
				shiftsChange.adminApproval = true
				shiftsChange.adminApprovalDate = new Date()
			} else {
				shiftsChange.analystApproval = true
				shiftsChange.analystApprovalDate = new Date()
			}
			await this.saveShiftsChange(shiftsChange)
		},
		async saveShiftsChange(shiftsChange) {
			try {
				if (shiftsChange.adminApproval && shiftsChange.analystApproval) {
					const oldShift = this.shifts.find((shift) => {
						return shift.id === parseInt(shiftsChange.oldShiftsId)
					})
					oldShift.userId = shiftsChange.newAnalystId
					await axios.put(generateUrl(`/apps/shifts/shifts/${oldShift.id}`), oldShift)
					const newShift = this.shifts.find((shift) => {
						return shift.id === parseInt(shiftsChange.newShiftsId)
					})
					if (newShift) {
						newShift.userId = shiftsChange.oldAnalystId
						await axios.put(generateUrl(`/apps/shifts/shifts/${newShift.id}`), newShift)
					}
					const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
					shiftResponse.data.forEach(shift => {
						shift.shiftsType = this.shiftsTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
						this.shifts.push(shift)
					})
				}
				await axios.put(generateUrl(`/apps/shifts/shiftsChange/${shiftsChange.id}`), shiftsChange)
				const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
				this.shiftsChanges = shiftsChangeResponse.data
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not save shifts Changes'))
			}
		},
		getShiftsDetailsByShiftsIdAsString(shiftId) {
			const shift = this.shifts.find((shift) => {
				return shift.id === parseInt(shiftId)
			})
			if (shift) {
				const date = new Date(shift.date)
				const options = {
					month: 'short',
					year: 'numeric',
					weekday: 'long',
					day: 'numeric',
				}
				const dateString = date.toLocaleDateString('de-DE', options)

				return shift.shiftsType.name + ' ' + dateString
			} else {
				return ''
			}
		},
		getDateString(date) {
			const dateObj = new Date(date)
			const options = {
				month: 'short',
				year: 'numeric',
				weekday: 'long',
				day: 'numeric',
			}
			const dateString = dateObj.toLocaleDateString('de-DE', options)
			const timeString = dateObj.toLocaleTimeString('de-DE')

			return dateString + ' ' + timeString
		},
	},
}
</script>
