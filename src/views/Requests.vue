<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<!--
  - View to display and add Requests
  -->
<template>
	<div>
		<!-- Top button bar -->
		<div class="top-bar">
			<div class="left">
				<div class="buttons-bar">
					<NcButton
						type="primary"
						@click="openDialog()">
						{{ t('shifts','New Request') }}
					</NcButton>
				</div>
			</div>
		</div>

		<ChangeRequestModal v-if="dialogOpen" @close="closeDialog" :is-admin="this.isAdmin" />

		<div class="requestTableContainer">
			<!-- Request still in progress -->
			<table>
				<thead>
					<tr>
						<th style="color: goldenrod">{{ t('shifts','In Progress') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr
						v-for="(changeRequest, i) in getInProgressShiftChangeRequests"
						:key="i">
						<td>
							<!-- <span>{{changeRequest}}</span>-->
							<div class="container shiftChangeRequestItem">
								<div class="row">
									<div class="col">
										<h2>{{changeRequest.oldAnalystId}}</h2>
									</div>
									<div class="col">
										<h2 v-if="changeRequest.type === 0">&#8644;</h2>
										<h2 v-else-if="changeRequest.type === 1">&#10140;</h2>
									</div>
									<div class="col">
										<h2>{{changeRequest.newAnalystId}}</h2>
									</div>
								</div>

								<div class="row" style="padding-top: 0; padding-bottom: 0">
									<div class="col">
										<div class="container">
											<div class="row">
												<div class="col">
													<div v-if="changeRequest.oldShift">
														<span>{{changeRequest.oldShift.shiftsType.name}}</span>
														<span>{{changeRequest.oldShift.date}}</span>
													</div>
													<div v-else>
														<span>{{t('shifts','Unknown shift type')}}</span>
														<span>N/A</span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col"></div>
									<div class="col">
										<div class="container">
											<div class="row">
												<div class="col">
													<div v-if="changeRequest.newShift">
														<span>{{changeRequest.newShift.shiftsType.name}}</span>
														<span>{{changeRequest.newShift.date}}</span>
													</div>
													<div v-else-if="changeRequest.type === 0">
														<span>{{t('shifts','Unknown shift type')}}</span>
														<span>N/A</span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- User approval -->
								<div class="row" style="padding-top: 0; padding-bottom: 0">
									<div class="col alignCenter">
										<div class="container">
											<div class="row">
												<div class="col alignCenter">
													<span>{{changeRequest.oldAnalystId}} {{t('shifts', 'has confirmed')}}</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col"></div>
									<div class="col alignCenter">
										<div class="container">
											<div class="row">
												<div class="col alignCenter">
													<span v-if="changeRequest.analystApproval">{{changeRequest.newAnalystId}} {{t('shifts', 'has confirmed')}}</span>
													<span v-else style="color: red">{{changeRequest.newAnalystId}} {{t('shifts', 'has not confirmed')}}</span>
												</div>
											</div>
										</div>
									</div>
									<div
										v-if="!isAdmin && currentUser === changeRequest.newAnalystId"
										class="col buttons-bar">
										<div class="right">
											<NcButton
												type="primary"
												:disabled="loading || changeRequest.analystApproval"
												@click="confirmUser(changeRequest)">
												<template #icon>
													<CheckBold></CheckBold>
												</template>
											</NcButton>
											<NcButton
												type="error"
												:disabled="loading"
												@click="cancelUser(changeRequest)">
												<template #icon>
													<CloseThick></CloseThick>
												</template>
											</NcButton>
										</div>
									</div>
								</div>

								<!-- Admin approval -->
								<div class="row" style="padding-top: 0; padding-bottom: 0">
									<div class="col alignCenter" style="padding-top: 0;">
										<h3 v-if="changeRequest.adminApproval">{{t('shifts', 'An admin has approved')}}</h3>
										<h3 v-else style="color: red">{{t('shifts', 'An admin has not approved')}}</h3>
									</div>
									<div class="col"></div>
									<div class="col"></div>
									<div
										v-if="isAdmin"
										class="col buttons-bar">
										<div class="right">
											<NcButton
												type="primary"
												:disabled="loading"
												@click="approveAdmin(changeRequest)">
												<template #icon>
													<CheckBold v-if="!changeRequest.adminApproval"></CheckBold>
													<BackupRestore v-else></BackupRestore>
												</template>
											</NcButton>
											<NcButton
												type="error"
												:disabled="loading"
												@click="cancelAdmin(changeRequest)">
												<template #icon>
													<CloseThick></CloseThick>
												</template>
											</NcButton>
											<NcButton
												type="error"
												:disabled="loading"
												@click="deleteAdmin(changeRequest)">
												<template #icon>
													<TrashCanOutline></TrashCanOutline>
												</template>
											</NcButton>
										</div>
									</div>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>

			<!-- Processed requests -->
			<table>
				<thead>
					<tr>
						<th style="color: green">{{ t('shifts','Processed') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr
						v-for="(changeRequest, i) in getProcessedShiftChangeRequests"
						:key="i">
						<td>
							<!-- <span>{{changeRequest}}</span>-->
							<div class="container shiftChangeRequestItem">
								<div class="row">
									<div class="col">
										<h2>{{changeRequest.oldAnalystId}}</h2>
									</div>
									<div class="col">
										<h2 v-if="changeRequest.type === 0">&#8644;</h2>
										<h2 v-else-if="changeRequest.type === 1">&#10140;</h2>
									</div>
									<div class="col">
										<h2>{{changeRequest.newAnalystId}}</h2>
									</div>
								</div>

								<div class="row" style="padding-top: 0; padding-bottom: 0">
									<div class="col">
										<div class="container">
											<div class="row">
												<div class="col">
													<div v-if="changeRequest.oldShift">
														<span>{{changeRequest.oldShift.shiftsType.name}}</span>
														<span>{{changeRequest.oldShift.date}}</span>
													</div>
													<div v-else>
														<span>{{t('shifts','Unknown shift type')}}</span>
														<span>N/A</span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col"></div>
									<div class="col">
										<div class="container">
											<div class="row">
												<div class="col">
													<div v-if="changeRequest.newShift">
														<span>{{changeRequest.newShift.shiftsType.name}}</span>
														<span>{{changeRequest.newShift.date}}</span>
													</div>
													<div v-else-if="changeRequest.type === 0">
														<span>{{t('shifts','Unknown shift type')}}</span>
														<span>N/A</span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- User approval -->
								<div class="row" style="padding-top: 0; padding-bottom: 0">
									<div class="col alignCenter">
										<div class="container">
											<div class="row">
												<div class="col alignCenter">
													<span>{{changeRequest.oldAnalystId}} {{t('shifts', 'has confirmed')}}</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col"></div>
									<div class="col alignCenter">
										<div class="container">
											<div class="row">
												<div class="col alignCenter">
													<span v-if="changeRequest.analystApproval">{{changeRequest.newAnalystId}} {{t('shifts', 'has confirmed')}}</span>
													<span v-else style="color: red">{{changeRequest.newAnalystId}} {{t('shifts', 'has not confirmed')}}</span>
												</div>
											</div>
										</div>
									</div>
									<div
										v-if="!isAdmin && currentUser === changeRequest.newAnalystId"
										class="col buttons-bar">
									</div>
								</div>

								<!-- Admin approval -->
								<div class="row" style="padding-top: 0; padding-bottom: 0">
									<div class="col alignCenter" style="padding-top: 0;">
										<h3 v-if="changeRequest.adminApproval">{{t('shifts', 'An admin has approved')}}</h3>
										<h3 v-else style="color: red">{{t('shifts', 'An admin has not approved')}}</h3>
									</div>
									<div class="col"></div>
									<div class="col"></div>
									<div
										v-if="isAdmin"
										class="col buttons-bar">
										<div class="right">
											<NcButton
												type="primary"
												:disabled="loading || !changeRequest.analystApproval"
												@click="approveAdmin(changeRequest)">
												<template #icon>
													<CheckBold v-if="!changeRequest.adminApproval"></CheckBold>
													<BackupRestore v-else></BackupRestore>
												</template>
											</NcButton>
											<NcButton
												type="error"
												:disabled="loading"
												@click="deleteAdmin(changeRequest)">
												<template #icon>
													<TrashCanOutline></TrashCanOutline>
												</template>
											</NcButton>
										</div>
									</div>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
import dayjs from 'dayjs'
import store from '../store'
import { mapGetters } from 'vuex'
import axios from '@nextcloud/axios'
import { NcButton } from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import { showError, showWarning } from '@nextcloud/dialogs'
import CheckBold from 'vue-material-design-icons/CheckBold'
import CloseThick from 'vue-material-design-icons/CloseThick'
import BackupRestore from 'vue-material-design-icons/BackupRestore'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline'
import ChangeRequestModal from '../components/ChangeRequests/ChangeRequestModal'

export default {
	name: 'Requests',
	components: {
		NcButton,
		CheckBold,
		CloseThick,
		BackupRestore,
		TrashCanOutline,
		ChangeRequestModal,
	},
	data() {
		return {
			dialogOpen: false,

			loading: false,
		}
	},
	computed: {
		...mapGetters({
			shiftsChanges: 'allShiftsChanges',
			analysts: 'allAnalysts',
			shiftsTypes: 'allShiftsTypes',
			shifts: 'allShifts',
			isAdmin: 'isAdmin',
			currentUser: 'currentUserId',

			getProcessedShiftChangeRequests: 'getProcessedShiftChangeRequests',
			getInProgressShiftChangeRequests: 'getInProgressShiftChangeRequests',
		}),
		// returns ShiftsChanges which are still in Progress and needs approval
		inProgressShiftsChanges() {
			return this.shiftsChanges.filter((shiftsChange) => {
				if (shiftsChange.oldShift === undefined || shiftsChange.newShift === undefined) {
					shiftsChange.oldShift = this.allShifts.find((shift) => shift.id.toString() === shiftsChange.oldShiftsId.toString())
					shiftsChange.newShift = this.allShifts.find((shift) => shift.id.toString() === shiftsChange.newShiftsId.toString())
				}
				const oldDate = dayjs(shiftsChange.oldShift.date)
				const newDate = dayjs(shiftsChange.newShift.date)
				return !(shiftsChange.adminApprovalDate !== '' && shiftsChange.analystApprovalDate !== '')
						&& dayjs().subtract(14, 'day').isBefore(oldDate)
						&& dayjs().subtract(14, 'day').isBefore(newDate)
			})
		},
		// returns ShiftsChanges which dont need further action
		doneShiftsChanges() {
			return this.shiftsChanges.filter((shiftsChange) => {
				const oldDate = dayjs(shiftsChange.oldShift.date)
				const newDate = dayjs(shiftsChange.newShift.date)
				return shiftsChange.adminApprovalDate !== '' && shiftsChange.analystApprovalDate !== ''
					&& dayjs().subtract(14, 'day').isBefore(oldDate)
					&& dayjs().subtract(14, 'day').isBefore(newDate)
			})
		},
		// returns if current user is an analyst
		isAnalyst() {
			let found = false
			for (let i = 0; i < this.analysts.length; i++) {
				if (this.analysts[i].uid === this.currentUser) {
					found = true
					break
				}
			}
			return found
		},
	},
	methods: {
		openDialog() {
			this.dialogOpen = true
		},
		closeDialog() {
			this.dialogOpen = false
		},

		confirmUser(changeRequest) {
			changeRequest.analystApproval = true
			this.updateChangeRequest(changeRequest)
		},
		cancelUser(changeRequest) {
			changeRequest.analystApproval = false
			this.updateChangeRequest(changeRequest)
		},
		approveAdmin(changeRequest) {
			if (changeRequest.adminApproval) {
				changeRequest.adminApproval = false
			} else {
				changeRequest.adminApproval = true
			}
			this.updateChangeRequest(changeRequest)
		},
		cancelAdmin(changeRequest) {
			changeRequest.adminApproval = false
			this.updateChangeRequest(changeRequest)
		},
		deleteAdmin(changeRequest) {
			this.loading = true
			store.dispatch('deleteShiftChangeRequest', changeRequest).catch((e) => {
				console.error('Failed to delete shift change request:', e)
				showError(t('shifts', 'Failed to delete shift change request'))
			}).finally(() => {
				store.dispatch('fetchShiftsChanges').finally(() => {
					this.loading = false
				})
			})
		},
		updateChangeRequest(changeRequest) {
			this.loading = true
			const cr = Object.assign({}, changeRequest)
			cr.adminApproval = changeRequest.adminApproval ? '1' : '0'
			cr.analystApproval = changeRequest.analystApproval ? '1' : '0'
			store.dispatch('updateShiftChangeRequest', cr).then(() => {
				console.info('Successfully update change request')
			}).catch((e) => {
				console.error('Failed to update change request:', e)
				showError(t('shifts', 'Failed to update change request'))
			}).finally(() => {
				store.dispatch('fetchShiftsChanges').finally(() => {
					this.loading = false
					store.dispatch('updateShifts')
				})
			})
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
			if (this.isAdmin && shiftsChange.newAnalystId === this.currentUser) {
				shiftsChange.adminApproval = '0'
				shiftsChange.adminApprovalDate = new Date()
				shiftsChange.analystApproval = '0'
				shiftsChange.analystApprovalDate = new Date()
			} else if (this.isAdmin) {
				shiftsChange.adminApproval = '0'
				shiftsChange.adminApprovalDate = new Date()
			} else {
				shiftsChange.analystApproval = '0'
				shiftsChange.analystApprovalDate = new Date()
			}
			await this.saveShiftsChange(shiftsChange)
		},
		async approved(shiftsChange) {
			if (this.isAdmin && shiftsChange.newAnalystId === this.currentUser) {
				shiftsChange.adminApproval = '1'
				shiftsChange.adminApprovalDate = new Date()
				shiftsChange.analystApproval = '1'
				shiftsChange.analystApprovalDate = new Date()
			} else if (this.isAdmin) {
				shiftsChange.adminApproval = '1'
				shiftsChange.adminApprovalDate = new Date()
			} else {
				shiftsChange.analystApproval = '1'
				shiftsChange.analystApprovalDate = new Date()
			}
			await this.saveShiftsChange(shiftsChange)
		},
		async saveShiftsChange(shiftsChange) {
			try {
				// checks for approval to change shifts
				// only done when both approvals are given
				if (shiftsChange.adminApproval === '1' && shiftsChange.analystApproval === '1') {
					const oldShift = this.shifts.find((shift) => {
						return shift.id === parseInt(shiftsChange.oldShiftsId)
					})
					oldShift.oldAnalystId = oldShift.userId
					oldShift.analystId = shiftsChange.newAnalystId
					oldShift.saveChanges = false

					await axios.put(generateUrl(`/apps/shifts/shifts/${oldShift.id}`), oldShift)
					const newShift = this.shifts.find((shift) => {
						return shift.id === parseInt(shiftsChange.newShiftsId)
					})
					if (newShift) {
						newShift.oldAnalystId = newShift.userId
						newShift.analystId = shiftsChange.oldAnalystId
						newShift.saveChanges = false

						await axios.put(generateUrl(`/apps/shifts/shifts/${newShift.id}`), newShift)
					}
					// fetches and updates shifts
					await this.$store.dispatch('updateShifts')
				}
				// updates shiftsChange
				await axios.put(generateUrl(`/apps/shifts/shiftsChange/${shiftsChange.id}`), shiftsChange)
				await this.$store.dispatch('updateShiftsChanges')
			} catch (e) {
				if (e.message.includes('Could not find corresponding Event')) {
					console.warn(e)
					showWarning(t('shifts'), 'Couldn\'t find corrensponding Calender-Entry')
				} else {
					console.error(e)
					showError(t('shifts', 'Could not save shifts Changes'))
				}
			}
		},
		// returns string detailing the Shift by given Shiftid
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
		// returns string of predetermined format with given date
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
		deleteRequest(request) {
			this.$store.dispatch('deleteRequest', request)
		},
	},
}
</script>

<style scoped lang="scss">
.requestTableContainer {
	width: 100%;
	display: flex;

	margin-top: 12px;

	table {
		width: 50%;

		thead {
			tr {
				th {
					width: 100%;

					font-size: 32px;
					font-weight: bold;
				}
			}
		}

		tbody {
			tr {
				td {
					padding: 5px;
				}
			}
		}
	}
}

h2, h3 {
	margin-top: 0;
	margin-bottom: 0;
}

.col.alignCenter {
	display: flex;
	align-items: center;
}

.container.shiftChangeRequestItem {
	width: 100%;

	.row {
		.col {
			padding-top: 0;
			padding-bottom: 0;
		}
		.col:first-child {
			width: 50%
		}
		.col:nth-child(2) {
			width: 25px
		}

		.col.buttons-bar {
			display: flex;
			flex: 1;
		}
	}
}
</style>
