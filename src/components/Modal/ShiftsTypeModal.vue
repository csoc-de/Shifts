<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -->

<template>
	<Modal
		size="large"
		:title="t('shifts', 'New Shiftstype')"
		@close="$emit('close')">
		<div class="shifts-type-modal">
			<!-- eslint-disable -->
			<v-text-field
				key="typeName"
				class="mb-1 p-1"
				hide-details
				:value="shiftsType.name"
				@change="updateName"
			></v-text-field>
			<Multiselect
				key="skillGroupSelect"
				:value="shiftsType.skillGroupId"
				:options="skillGroups"
				track-by="id"
				label="name"
				@change="updateSkillGroup" />
			<v-menu
				key="color"
				ref="colorMenu"
				v-model="colorMenu"
				:close-on-content-click="false"
				:close-on-click="true"
				:nudge-right="40"
				transition="scale-transition"
				offset-y
				attach
				absolute
				min-width="290px">
				<template v-slot:activator="{ on, attrs }">
					<v-text-field
						:value="shiftsType.color"
						:label="t('shifts', 'Calendar Color')"
						v-bind="attrs"
						v-on="on">
					</v-text-field>
				</template>
				<v-color-picker
					dot-size="25"
					mode="hexa"
					swatches-max-height="200"
					:value="color.hexa"
					@update:color="changeColor">
				</v-color-picker>
				<v-btn color="primary" @click="colorMenu = false">
					Cancel
				</v-btn>
				<v-btn color="primary" @click="saveColor()">
					Ok
				</v-btn>
			</v-menu>
			<div v-if="showTimeSelector">
				<v-menu
					key="startTimestamp"
					ref="startMenu"
					v-model="startMenu"
					:close-on-click="true"
					:close-on-content-click="false"
					:nudge-right="40"
					transition="scale-transition"
					offset-y
					attach
					absolute
					max-width="290px"
					min-width="290px">
					<template v-slot:activator="{ on, attrs }">
						<v-text-field
							:value="shiftsType.startTimestamp"
							:label="t('shifts', 'Start Time')"
							v-bind="attrs"
							v-on="on">
						</v-text-field>
					</template>
					<v-time-picker
						v-if="startMenu"
						:value="shiftsType.startTimestamp"
						format="24hr"
						full-width
						@change="changeStart">
					</v-time-picker>
				</v-menu>
				<v-menu
					key="stopTimestamp"
					ref="stopMenu"
					v-model="stopMenu"
					:close-on-content-click="false"
					:close-on-click="true"
					:nudge-right="40"
					transition="scale-transition"
					offset-y
					attach
					absolute
					max-width="290px"
					min-width="290px">
					<template v-slot:activator="{ on, attrs }">
						<v-text-field
							:value="shiftsType.stopTimestamp"
							:label="t('shifts', 'Stop Time')"
							v-bind="attrs"
							v-on="on">
						</v-text-field>
					</template>
					<v-time-picker
						v-if="stopMenu"
						:value="shiftsType.stopTimestamp"
						format="24hr"
						full-width
						@change="changeStop">
					</v-time-picker>
				</v-menu>
			</div>
			<div v-if="!showTimeSelector">
				<v-text-field
					key="numShifts"
					:label="t('shifts', 'weekly Shifts')"
					type="number"
					min="-1"
					max="10"
					hide-details
					:value="shiftsType.moRule"
					@change="changeMoRule"
				></v-text-field>
			</div>
			<v-checkbox
				key="isWeekly"
				v-model="shiftsType.isWeekly"
				:label="t('shifts','Weekly')"
				@change="updateIsWeekly">
			</v-checkbox>
			<v-expansion-panels
				key="rules"
				:flat="true"
				v-if="showTimeSelector">
				<v-expansion-panel>
					<v-expansion-panel-header>{{ t('shifts', 'Rules')}}</v-expansion-panel-header>
					<v-expansion-panel-content>
						<v-text-field
							:label="t('shifts', 'Monday')"
							type="number"
							min="-1"
							max="10"
							hide-details
							:value="shiftsType.moRule"
							@change="changeMoRule"
						></v-text-field>
						<v-text-field
							:label="t('shifts', 'Tuesday')"
							type="number"
							min="-1"
							max="10"
							hide-details
							:value="shiftsType.tuRule"
							@change="changeTuRule"
						></v-text-field>
						<v-text-field
							:label="t('shifts', 'Wednesday')"
							type="number"
							min="-1"
							max="10"
							hide-details
							:value="shiftsType.weRule"
							@change="changeWeRule"
						></v-text-field>
						<v-text-field
							:label="t('shifts', 'Thursday')"
							type="number"
							min="-1"
							max="10"
							hide-details
							:value="shiftsType.thRule"
							@change="changeThRule"
						></v-text-field>
						<v-text-field
							:label="t('shifts', 'Friday')"
							type="number"
							min="-1"
							max="10"
							hide-details
							:value="shiftsType.frRule"
							@change="changeFrRule"
						></v-text-field>
						<v-text-field
							:label="t('shifts', 'Saturday')"
							type="number"
							min="-1"
							max="10"
							hide-details
							:value="shiftsType.saRule"
							@change="changeSaRule"
						></v-text-field>
						<v-text-field
							:label="t('shifts', 'Sunday')"
							type="number"
							min="-1"
							max="10"
							hide-details
							:value="shiftsType.soRule"
							@change="changeSoRule"
						></v-text-field>
					</v-expansion-panel-content>
				</v-expansion-panel>
			</v-expansion-panels>
			<v-row
				class="float_right">
				<v-btn @click="$emit('close')">
					{{ t('shifts','Cancel') }}
				</v-btn>
				<v-btn color="#03a9f4"
					   @click="save">
					{{ t('shifts','Save') }}
				</v-btn>
			</v-row>
			<!-- eslint-enable -->
		</div>
	</Modal>
</template>

<script>
import Modal from '@nextcloud/vue/dist/Components/Modal'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import { mapGetters } from 'vuex'
export default {
	name: 'ShiftsTypeModal',
	components: {
		Modal,
		Multiselect,
	},
	data() {
		return {
			startMenu: false,
			stopMenu: false,
			colorMenu: false,
			color: '',
		}
	},
	computed: {
		...mapGetters({
			shiftsType: 'shiftsTypeInstance',
			skillGroups: 'getSkillGroups',
		}),
		showTimeSelector() {
			return !this.shiftsType.isWeekly
		},
	},
	methods: {
		updateName(name) {
			this.$store.commit('changeName', name)
		},
		changeStart(start) {
			this.startMenu = false
			this.$store.commit('changeStart', start)
		},
		changeStop(stop) {
			this.stopMenu = false
			this.$store.commit('changeStop', stop)
		},
		changeColor(color) {
			this.color = color
		},
		changeMoRule(value) {
			this.$store.commit('changeMoRule', value)
		},
		changeTuRule(value) {
			this.$store.commit('changeTuRule', value)
		},
		changeWeRule(value) {
			this.$store.commit('changeWeRule', value)
		},
		changeThRule(value) {
			this.$store.commit('changeThRule', value)
		},
		changeFrRule(value) {
			this.$store.commit('changeFrRule', value)
		},
		changeSaRule(value) {
			this.$store.commit('changeSaRule', value)
		},
		changeSoRule(value) {
			this.$store.commit('changeSoRule', value)
		},
		saveColor() {
			this.colorMenu = false
			this.$store.commit('changeColor', this.color.hexa)
		},
		updateSkillGroup(skillGroup) {
			this.$store.commit('changeSkillGroupId', skillGroup)
		},
		updateIsWeekly(value) {
			this.$store.commit('changeIsWeekly', value)
		},
		save() {
			this.$store.dispatch('saveCurrentShiftsType')
			this.$emit('saved')
		},
	},
}
</script>
