<template>
	<Modal
		size="large"
		:title="t('shifts', 'Neue Anfrage')"
		@close="$emit('close')">
		<div class="modal__content">
			<h1>{{ t('shifts', 'Neue Anfrage') }}</h1>
			<v-tabs v-model="tab">
				<v-tab v-for="item in tabItems"
					:key="item">
					{{ item }}
				</v-tab>
			</v-tabs>
			<!--eslint-disable-->
			<v-tabs-items v-model="tab">
				<v-tab-item>
					<v-row>
						<v-col
							cols="12"
							md="6">
							<v-autocomplete
								id="test"
								v-model="selectedOldAnalyst"
								:items="excludeNewAnalystSelected"
								:disabled="!isAdmin"
								hide-selected
								deletable-chips
								item-text="date"
								item-value="id"
								:filter="analystFilter"
								:label="t('shifts','Wähle alten Analysten')"
								attach>
								<template v-slot:selection="data">
									<v-chip
										v-bind="data.attrs"
										:input-value="data.selected"
										close
										close-icon="icon-close"
										@click="data.select"
										@click:close="removeOldAnalyst()">
										<Avatar :user="data.item.uid" :display-name="data.item.name" />
										{{ data.item.name }}
									</v-chip>
								</template>
								<template v-slot:item="data">
									<template v-if="typeof data.item !== 'object'">
										<v-list-item-content v-text="data.item"></v-list-item-content>
									</template>
									<template v-else>
										<Avatar :user="data.item.uid" :display-name="data.item.name" />
										<v-list-item-content>
											<v-list-item-title v-html="data.item.name"></v-list-item-title>
											<v-list-item-subtitle v-html="data.item.email"></v-list-item-subtitle>
										</v-list-item-content>
									</template>
								</template>
							</v-autocomplete>
						</v-col>
						<v-col
							cols="12"
							md="6">
							<v-autocomplete
								v-model="selectedNewAnalyst"
								:items="excludeOldAnalystSelected"
								hide-selected
								deletable-chips
								item-text="date"
								item-value="id"
								:filter="analystFilter"
								:label="t('shifts','Wähle neuen Analysten')"
								attach>
								<template v-slot:selection="data">
									<v-chip
										v-bind="data.attrs"
										:input-value="data.selected"
										close
										close-icon="icon-close"
										@click="data.select"
										@click:close="removeNewAnalyst()">
										<Avatar :user="data.item.uid" :display-name="data.item.name" />
										{{ data.item.name }}
									</v-chip>
								</template>
								<template v-slot:item="data">
									<template v-if="typeof data.item !== 'object'">
										<v-list-item-content v-text="data.item"></v-list-item-content>
									</template>
									<template v-else>
										<Avatar :user="data.item.uid" :display-name="data.item.name" />
										<v-list-item-content>
											<v-list-item-title v-html="data.item.name"></v-list-item-title>
											<v-list-item-subtitle v-html="data.item.email"></v-list-item-subtitle>
										</v-list-item-content>
									</template>
								</template>
							</v-autocomplete>
						</v-col>
					</v-row>
					<v-row>
						<v-col
							cols="12"
							md="6">
							<v-autocomplete
								v-if="selectedOldAnalyst"
								v-model="oldAnalystSelectedShifts"
								:items="oldAnalystShifts"
								multiple
								hide-selected
								deletable-chips
								item-text="date"
								item-value="id"
								:filter="shiftsFilter"
								:label="t('shifts','Wähle die Schichten des alten Analysten')"
								attach>
								<template v-slot:selection="data">
									<v-chip
										v-bind="data.attrs"
										:input-value="data.selected"
										close
										close-icon="icon-close"
										@click="data.select"
										@click:close="removeOldAnalystShift(data.item)">
										{{ data.item.shiftsType.name + " " + data.item.date }}
									</v-chip>
								</template>
								<template v-slot:item="data">
									<template v-if="typeof data.item !== 'object'">
										<v-list-item-content v-text="data.item"></v-list-item-content>
									</template>
									<template v-else>
										<v-list-item-content>
											<v-list-item-title v-html="data.item.shiftsType.name"></v-list-item-title>
											<v-list-item-subtitle v-html="data.item.date"></v-list-item-subtitle>
										</v-list-item-content>
									</template>
								</template>
							</v-autocomplete>
						</v-col>
						<v-col
							cols="12"
							md="6">
							<v-autocomplete
								v-if="selectedNewAnalyst"
								v-model="newAnalystSelectedShifts"
								:items="newAnalystShifts"
								multiple
								hide-selected
								deletable-chips
								item-text="date"
								item-value="id"
								:filter="shiftsFilter"
								:disabled="selectedNewAnalyst === null"
								:label="t('shifts','Wähle Schichten des neuen Analysten')"
								attach>
								<template v-slot:selection="data">
									<v-chip
										v-bind="data.attrs"
										:input-value="data.selected"
										close
										close-icon="icon-close"
										@click="data.select"
										@click:close="removeNewAnalystShift(data.item)">
										{{ data.item.shiftsType.name + " " + data.item.date }}
									</v-chip>
								</template>
								<template v-slot:item="data">
									<template v-if="typeof data.item !== 'object'">
										<v-list-item-content v-text="data.item"></v-list-item-content>
									</template>
									<template v-else>
										<v-list-item-content>
											<v-list-item-title v-html="data.item.shiftsType.name"></v-list-item-title>
											<v-list-item-subtitle v-html="data.item.date"></v-list-item-subtitle>
										</v-list-item-content>
									</template>
								</template>
							</v-autocomplete>
						</v-col>
						<v-col
							cols="12"
							md="12">
							<v-textarea
								name="desc"
								:label=" t('shifts', 'Beschreibung')"
								:hint=" t('shifts', 'Beschreibung oder Grund')"
								:value="desc">
							</v-textarea>
						</v-col>
					</v-row>
				</v-tab-item>
				<v-tab-item>
					<v-row>
						<v-col
							cols="12"
							md="6">
							<v-autocomplete
								v-model="selectedOldAnalyst"
								:items="excludeNewAnalystSelected"
								:disabled="!isAdmin"
								hide-selected
								deletable-chips
								item-text="date"
								item-value="id"
								:filter="analystFilter"
								:label="t('shifts','Wähle alten Analysten')"
								attach>
								<template v-slot:selection="data">
									<v-chip
										v-bind="data.attrs"
										:input-value="data.selected"
										close
										close-icon="icon-close"
										@click="data.select"
										@click:close="removeOldAnalyst()">
										<Avatar :user="data.item.uid" :display-name="data.item.name" />
										{{ data.item.name }}
									</v-chip>
								</template>
								<template v-slot:item="data">
									<template v-if="typeof data.item !== 'object'">
										<v-list-item-content v-text="data.item"></v-list-item-content>
									</template>
									<template v-else>
										<Avatar :user="data.item.uid" :display-name="data.item.name" />
										<v-list-item-content>
											<v-list-item-title v-html="data.item.name"></v-list-item-title>
											<v-list-item-subtitle v-html="data.item.email"></v-list-item-subtitle>
										</v-list-item-content>
									</template>
								</template>
							</v-autocomplete>
						</v-col>
						<v-col
							cols="12"
							md="6">
							<v-autocomplete
								v-model="selectedNewAnalyst"
								:items="excludeOldAnalystSelected"
								hide-selected
								deletable-chips
								item-text="date"
								item-value="id"
								:filter="analystFilter"
								:label="t('shifts','Wähle neuen Analysten')"
								attach>
								<template v-slot:selection="data">
									<v-chip
										v-bind="data.attrs"
										:input-value="data.selected"
										close
										close-icon="icon-close"
										@click="data.select"
										@click:close="removeNewAnalyst()">
										<Avatar :user="data.item.uid" :display-name="data.item.name" />
										{{ data.item.name }}
									</v-chip>
								</template>
								<template v-slot:item="data">
									<template v-if="typeof data.item !== 'object'">
										<v-list-item-content v-text="data.item"></v-list-item-content>
									</template>
									<template v-else>
										<Avatar :user="data.item.uid" :display-name="data.item.name" />
										<v-list-item-content>
											<v-list-item-title v-html="data.item.name"></v-list-item-title>
											<v-list-item-subtitle v-html="data.item.email"></v-list-item-subtitle>
										</v-list-item-content>
									</template>
								</template>
							</v-autocomplete>
						</v-col>
					</v-row>
					<v-row>
						<v-col
							cols="12"
							md="6">
							<v-autocomplete
								v-if="selectedOldAnalyst"
								v-model="oldAnalystSelectedShifts"
								:items="oldAnalystShifts"
								multiple
								hide-selected
								deletable-chips
								item-text="date"
								item-value="id"
								:filter="shiftsFilter"
								:label="t('shifts','Wähle die Schichten des alten Analysten')"
								attach>
								<template v-slot:selection="data">
									<v-chip
										v-bind="data.attrs"
										:input-value="data.selected"
										close
										close-icon="icon-close"
										@click="data.select"
										@click:close="removeOldAnalystShift(data.item)">
										{{ data.item.shiftsType.name + " " + data.item.date }}
									</v-chip>
								</template>
								<template v-slot:item="data">
									<template v-if="typeof data.item !== 'object'">
										<v-list-item-content v-text="data.item"></v-list-item-content>
									</template>
									<template v-else>
										<v-list-item-content>
											<v-list-item-title v-html="data.item.shiftsType.name"></v-list-item-title>
											<v-list-item-subtitle v-html="data.item.date"></v-list-item-subtitle>
										</v-list-item-content>
									</template>
								</template>
							</v-autocomplete>
						</v-col>
						<v-spacer></v-spacer>
					</v-row>
					<v-row>
						<v-col
							cols="12"
							md="12">
							<v-textarea
								name="desc"
								:label=" t('shifts', 'Beschreibung')"
								:hint=" t('shifts', 'Beschreibung oder Grund')"
								:value="desc">
							</v-textarea>
						</v-col>
					</v-row>
				</v-tab-item>
			</v-tabs-items>
			<v-row
				class="float_right">
				<v-btn @click="$emit('close')">
					{{ t('shifts','Abbrechen') }}
				</v-btn>
				<v-btn color="#03a9f4"
					@click="save()"
					:disabled="saveDisabled">
					{{ t('shifts','Speichern') }}
				</v-btn>
			</v-row>
			<!--eslint-enable-->
		</div>
	</Modal>
</template>

<script>
import Modal from '@nextcloud/vue/dist/Components/Modal'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'RequestsModal',
	components: {
		Modal,
		Avatar,
	},
	props: {
		isAdmin: {
			type: Boolean,
			required: true,
		},
		shifts: {
			type: Array,
			required: true,
		},
		analysts: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			selectedNewAnalyst: null,
			selectedOldAnalyst: null,
			oldAnalystSelectedShifts: [],
			newAnalystSelectedShifts: [],
			mutableAnalysts: this.analysts,
			tab: t('shifts', 'Tauschen'),
			desc: '',
			tabItems: [t('shifts', 'Tauschen'), t('shifts', 'Angebot')],
		}
	},
	computed: {
		oldAnalystShifts() {
			return this.shifts.filter((shift) => {
				return shift.userId === this.selectedOldAnalyst.uid
			})
		},
		newAnalystShifts() {
			return this.shifts.filter((shift) => {
				return shift.userId === this.selectedNewAnalyst.uid
			})
		},
		excludeOldAnalystSelected() {
			 if (this.selectedOldAnalyst) {
				return this.mutableAnalysts.filter((analyst) => {
					return analyst.uid !== this.selectedOldAnalyst.uid
				})
			} else {
				return this.mutableAnalysts
			}
		},
		excludeNewAnalystSelected() {
			if (this.selectedNewAnalyst) {
				return this.mutableAnalysts.filter((analyst) => {
					return analyst.uid !== this.selectedNewAnalyst.uid
				})
			} else {
				return this.mutableAnalysts
			}
		},
		saveDisabled() {
			const shiftsOk = (this.tab === t('shifts', 'Tauschen') && this.newAnalystSelectedShifts.length === this.oldAnalystSelectedShifts.length)
				|| (this.tab === t('shifts', 'Angebot') && this.newAnalystSelectedShifts.length > 0)
			const analystsOk = this.selectedOldAnalyst && this.selectedNewAnalyst

			return shiftsOk && analystsOk
		},
	},
	async mounted() {
		let currentUser
		try {
			const shiftsChangeResponse = await axios.get(generateUrl('/apps/shifts/shiftsChange'))
			const currentUserIdResponse = await axios.get(generateUrl('/apps/shifts/getCurrentUserId'))
			currentUser = currentUserIdResponse.data
			this.shiftsChanges = shiftsChangeResponse.data
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts'))
		}
		if (!this.isAdmin) {
			this.selectedOldAnalyst = this.analysts.find((analyst) => analyst.uid === currentUser)
		}
		this.isLoading = false
	},
	methods: {
		removeOldAnalyst() {
			this.selectedOldAnalyst = null
		},
		removeNewAnalyst() {
			this.selectedNewAnalyst = null
		},
		removeOldAnalystShift(item) {
			const index = this.oldAnalystSelectedShifts.indexOf(item.id)
			console.log(index)
			console.log(this.oldAnalystSelectedShifts)
			if (index >= 0) this.oldAnalystSelectedShifts.splice(index, 1)
		},
		removeNewAnalystShift(item) {
			const index = this.newAnalystSelectedShifts.indexOf(item.id)
			console.log(index)
			console.log(this.newAnalystSelectedShifts)
			if (index >= 0) this.newAnalystSelectedShifts.splice(index, 1)
		},
		analystFilter(item, queryText, itemText) {
			return (
				item.name.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
				|| item.email.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
			)
		},
		shiftsFilter(item, queryText, itemText) {
			return (
				item.shiftsType.name.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
				|| item.date.toLocaleLowerCase().indexOf(queryText.toLocaleLowerCase()) > -1
			)
		},
		async save() {
			try {
				const oldAnalystId = this.selectedOldAnalyst.uid
				const newAnalystId = this.selectedNewAnalyst.uid
				const desc = this.desc
				const type = this.tab
				const newShiftsChanges = []
				await Promise.all(this.oldAnalystSelectedShifts.map(async(shiftId, index) => {
					const oldShiftsId = shiftId
					const newShiftsId = type === 0 ? this.newAnalystSelectedShifts[index] : -1
					const newShiftsChange = {
						oldAnalystId,
						newAnalystId,
						adminApproval: '0',
						adminApprovalDate: '',
						analystApproval: '0',
						analystApprovalDate: '',
						desc,
						type,
						oldShiftsId,
						newShiftsId,
					}
					console.log(newShiftsChange)
					await axios.post(generateUrl('/apps/shifts/shiftsChange'), newShiftsChange)
					newShiftsChanges.push(newShiftsChange)
				}))
				this.$emit('saved', newShiftsChanges)
			} catch (e) {
				console.error(e)
				showError(t('shifts', 'Could not fetch shifts'))
			}
		},
	},
}
</script>
