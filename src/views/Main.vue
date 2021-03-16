<template>
	<v-app>
		<AppContent>
			<div id="content" class="app-shifts">
				<!--eslint-disable-->
				<v-app-bar
					dense
					flat
					absolute
					class="correction_margin">
					<v-app-bar-nav-icon></v-app-bar-nav-icon>
					<v-toolbar-title class="ml-2 toolbar_title">
						Schichten
					</v-toolbar-title>
					<v-tabs
						v-model="tab">
						<v-tab
							v-for="item in tabItems"
							:key="item">
							{{ item }}
						</v-tab>
					</v-tabs>
				</v-app-bar>
				<v-tabs-items v-model="tab">
					<v-tab-item value="anfragen">
						{{ t('shifts', 'test') }}
					</v-tab-item>
				</v-tabs-items>
			</div>
			<div>
				<ShiftsTab
					:is-admin="isAdmin">
				</ShiftsTab>
			</div>
			<!--eslint-enable-->
		</AppContent>
	</v-app>
</template>
<script>
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import ShiftsTab from './ShiftsTab'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
	name: 'Main',
	components: {
		AppContent,
		ShiftsTab,
	},
	data() {
		return {
			isAdmin: false,
			loading: true,
			tab: null,
			analysts: [],
			shifts: [],
			shiftTypes: [],
			tabItems: [t('shifts', 'schichten'), t('shifts', 'anfragen')],
		}
	},
	async mounted() {
		try {
			const shiftResponse = await axios.get(generateUrl('/apps/shifts/shifts'))
			const shiftTypeResponse = await axios.get(generateUrl('/apps/shifts/shiftsType'))
			const analystsResponse = await axios.get(generateUrl('/apps/shifts/getAllAnalysts'))
			const isAdminResponse = await axios.get(generateUrl('/apps/shifts/checkAdmin'))
			this.analysts = analystsResponse.data
			this.shiftTypes = shiftTypeResponse.data
			shiftResponse.data.forEach(shift => {
				shift.shiftsType = this.shiftTypes.find((shiftType) => shiftType.id.toString() === shift.shiftTypeId)
				this.shifts.push(shift)
			})
			this.isAdmin = isAdminResponse.data
		} catch (e) {
			console.error(e)
			showError(t('shifts', 'Could not fetch shifts'))
		}
		this.loading = false
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
