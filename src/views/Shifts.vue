<!--
  - View to display Shifts and add Shifts or Shiftstypes if admin is current user
  -->
<template>
	<div class="shifts_content">
		<div v-if="isAdmin">
			<!--eslint-disable-->
			<v-menu	 v-if="!loading"
				v-model="shiftOpen"
				:close-on-content-click="false"
				:nudge-width="200"
				attach
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
					<NewShift @cancel="closeNewShift">
					</NewShift>
				</v-layout>
			</v-menu>
			<v-menu	 v-if="!loading"
				v-model="shiftTypeOpen"
				:close-on-content-click="false"
				:nudge-width="200"
				attach
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
		<Calendar v-if="!loading" />
		<!-- eslint-enable-->
	</div>
</template>
<script>
import Calendar from '../components/Calendar'
import NewShiftType from './NewShiftType'
import NewShift from './NewShift'
import { generateUrl } from '@nextcloud/router'
import { showError, showWarning } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { mapGetters } from 'vuex'

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
			loading: true,
			shiftTypeOpen: false,
			shiftOpen: false,
			shiftsService: null,
		}
	},
	computed: {
		...mapGetters({
			isAdmin: 'isAdmin',
		}),
	},
	async mounted() {
		await this.$store.dispatch('setup')
		this.loading = false
	},
	methods: {
		closeNewShiftType() {
			this.shiftTypeOpen = false
		},
		closeNewShift() {
			console.log('test')
			this.shiftOpen = false
		},
		async newShiftType(shiftType) {
			if (shiftType.name) {
				await this.createShiftType(shiftType)
			} else {
				console.log('No Name for ShiftType')
				showWarning(t('shifts', 'No Name for Shift-Type given'))
			}
		},
		// saves created Shiftstype
		async createShiftType(shiftType) {
			try {
				await axios.post(generateUrl('/apps/shifts/shiftsType'), shiftType)
				await this.$store.dispatch('updateShiftsTypes')
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
