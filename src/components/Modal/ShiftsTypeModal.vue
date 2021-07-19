<template>
	<Modal
		size="large"
		:title="t('shifts', 'Neuer Schichttyp')"
		@close="$emit('close')">
		<div class="shifts-type-modal">
			<!-- eslint-disable -->
			<v-text-field
				class="mb-1 p-1"
				hide-details
				:value="shiftsType.name"
				@change="updateName"
			></v-text-field>
			<Multiselect :value="shiftsType.skillGroupId"
				:options="skillGroups"
				track-by="id"
				label="name"
				@change="updateSkillGroup" />
			<v-menu
				ref="startMenu"
				v-model="startMenu"
				:close-on-click="true"
				:close-on-content-click="false"
				:nudge-right="40"
				transition="scale-transition"
				offset-y
				attach
				max-width="290px"
				min-width="290px">
				<template v-slot:activator="{ on, attrs }">
					<v-text-field
						:value="shiftsType.startTimestamp"
						label=" Start Time"
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
				ref="stopMenu"
				v-model="stopMenu"
				:close-on-content-click="false"
				:close-on-click="true"
				:nudge-right="40"
				transition="scale-transition"
				offset-y
				attach
				max-width="290px"
				min-width="290px">
				<template v-slot:activator="{ on, attrs }">
					<v-text-field
						:value="shiftsType.stopTimestamp"
						label=" Stop Time"
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
			<v-menu
				ref="colorMenu"
				v-model="colorMenu"
				:close-on-content-click="false"
				:close-on-click="true"
				:nudge-right="40"
				transition="scale-transition"
				offset-y
				top
				attach
				min-width="290px">
				<template v-slot:activator="{ on, attrs }">
					<v-text-field
						:value="shiftsType.color"
						label="Calendar Color"
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
			<v-expansion-panels
				flat>
				<v-expansion-panel>
					<v-expansion-panel-header>{{ t('shifts', 'Regeln')}}</v-expansion-panel-header>
					<v-expansion-panel-content>
						<v-list>
							<v-list-item-group>
								<v-list-item>
									<v-list-item-content>
										<v-text-field
											:label="t('shifts', 'Montag')"
											type="number"
											min="-1"
											max="10"
											hide-details
											:value="shiftsType.moRule"
											@change="(value) => changeRule('mo', value)"
										></v-text-field>
									</v-list-item-content>
								</v-list-item>
								<v-list-item>
									<v-list-item-content>
										<v-text-field
											:label="t('shifts', 'Dienstag')"
											type="number"
											min="-1"
											max="10"
											hide-details
											:value="shiftsType.tuRule"
											@change="(value) => changeRule('tu', value)"
										></v-text-field>
									</v-list-item-content>
								</v-list-item>
								<v-list-item>
									<v-list-item-content>
										<v-text-field
											:label="t('shifts', 'Mittwoch')"
											type="number"
											min="-1"
											max="10"
											hide-details
											:value="shiftsType.weRule"
											@change="(value) => changeRule('we', value)"
										></v-text-field>
									</v-list-item-content>
								</v-list-item>
								<v-list-item>
									<v-list-item-content>
										<v-text-field
											:label="t('shifts', 'Donnerstag')"
											type="number"
											min="-1"
											max="10"
											hide-details
											:value="shiftsType.thRule"
											@change="(value) => changeRule('th', value)"
										></v-text-field>
									</v-list-item-content>
								</v-list-item>
								<v-list-item>
									<v-list-item-content>
										<v-text-field
											:label="t('shifts', 'Freitag')"
											type="number"
											min="-1"
											max="10"
											hide-details
											:value="shiftsType.frRule"
											@change="(value) => changeRule('fr', value)"
										></v-text-field>
									</v-list-item-content>
								</v-list-item>
								<v-list-item>
									<v-list-item-content>
										<v-text-field
											:label="t('shifts', 'Samstag')"
											type="number"
											min="-1"
											max="10"
											hide-details
											:value="shiftsType.saRule"
											@change="(value) => changeRule('sa', value)"
										></v-text-field>
									</v-list-item-content>
								</v-list-item>
								<v-list-item>
									<v-list-item-content>
										<v-text-field
											:label="t('shifts', 'Sonntag')"
											type="number"
											min="-1"
											max="10"
											hide-details
											:value="shiftsType.soRule"
											@change="(value) => changeRule('so', value)"
										></v-text-field>
									</v-list-item-content>
								</v-list-item>
							</v-list-item-group>
						</v-list>
					</v-expansion-panel-content>
				</v-expansion-panel>
			</v-expansion-panels>
			<v-row
				class="float_right">
				<v-btn @click="$emit('close')">
					{{ t('shifts','Abbrechen') }}
				</v-btn>
				<v-btn color="#03a9f4"
					   @click="save">
					{{ t('shifts','Speichern') }}
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
		changeRule(rule, value) {
			this.$store.commit('changeRule', rule, value)
		},
		saveColor() {
			this.colorMenu = false
			this.$store.commit('changeColor', this.color.hexa)
		},
		updateSkillGroup(skillGroup) {
			this.$store.commit('changeSkillGroupId', skillGroup)
		},
		save() {
			this.$store.dispatch('saveCurrentShiftsType')
			this.$emit('saved')
		},
	},
}
</script>
