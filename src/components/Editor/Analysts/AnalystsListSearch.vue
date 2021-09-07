<!--
  - @copyright Copyright (c) 2019 Georg Ehrke <oc.list@georgehrke.com>
  -
  - @author Georg Ehrke <oc.list@georgehrke.com>
  - @modified Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<!--
  - Component responsible for searching in the AnalystsList-Component
  -->
<template>
	<Multiselect
		class="invitees-search"
		:options="matches"
		:searchable="true"
		:internal-search="false"
		:max-height="600"
		:show-no-results="true"
		:show-no-options="false"
		:placeholder="placeholder"
		:class="{ 'showContent': inputGiven, 'icon-loading': isLoading }"
		open-direction="bottom"
		track-by="email"
		label="dropdownName"
		@search-change="findAnalysts"
		@select="addAnalyst">
		<template slot="singleLabel" slot-scope="props">
			<div class="invitees-search-list-item">
				<Avatar v-if="props.option.isUser" :user="props.option.avatar" :display-name="props.option.dropdownName" />
				<Avatar v-if="!props.option.isUser" :url="props.option.avatar" :display-name="props.option.dropdownName" />
				<div class="invitees-search-list-item__label invitees-search-list-item__label--single-email">
					<div>
						{{ props.option.dropdownName }}
					</div>
				</div>
			</div>
		</template>
		<template slot="option" slot-scope="props">
			<div class="invitees-search-list-item">
				<Avatar v-if="props.option.isUser" :user="props.option.avatar" :display-name="props.option.dropdownName" />
				<Avatar v-if="!props.option.isUser" :url="props.option.avatar" :display-name="props.option.dropdownName" />
				<div class="invitees-search-list-item__label invitees-search-list-item__label--single-email">
					<div>
						{{ props.option.dropdownName }}
					</div>
				</div>
			</div>
		</template>
	</Multiselect>
</template>

<script>
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import debounce from 'debounce'
import { mapGetters } from 'vuex'
export default {
	name: 'AnalystsListSearch',
	components: {
		Avatar,
		Multiselect,
	},
	props: {
		alreadyInvitedEmails: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			isLoading: false,
			inputGiven: false,
			matches: [],
		}
	},
	computed: {
		...mapGetters({
			currentShiftsType: 'getCurrentShiftsType',
			allAnalysts: 'allAnalysts',
		}),
		// Placeholder String
		placeholder() {
			return this.$t('shifts', 'Search for Emails or Users')
		},
		// Placeholder String for no Matches
		noResult() {
			return this.$t('shifts', 'No match found')
		},
	},
	watch: {
		// returns List of Emails from invited analysts
		// eslint-disable-next-line
		alreadyInvitedEmails: function(newVal, oldVal) {
			const result = this.allAnalysts.filter((analyst) => {
				return !newVal.includes(analyst.email)
			})
			this.matches.length = 0
			this.matches.push(...result)
		},
		currentShiftsType(newVal, oldVal) {
			this.matches.length = 0
			this.allAnalysts.forEach((analyst) => {
				let name
				if (analyst.name) {
					name = analyst.name
				} else if (analyst.name && analyst.email) {
					name = `${analyst.name} (${analyst.email})`
				} else {
					name = analyst.email
				}
				const a = {
					calendarUserType: 'INDIVIDUAL',
					commonName: analyst.name,
					email: analyst.email,
					isUser: true,
					avatar: analyst.uid,
					dropdownName: name,
					userId: analyst.uid,
				}
				console.log(analyst)
				console.log(this.currentShiftsType)
				if (analyst.skillGroup >= this.currentShiftsType.skillGroupId) {
					this.matches.push(a)
				}
			})
		},
	},
	beforeMount() {
		// Matches incoming Data-Fields to Corresponding Fields
		this.allAnalysts.forEach((analyst) => {
			let name
			if (analyst.name) {
				name = analyst.name
			} else if (analyst.name && analyst.email) {
				name = `${analyst.name} (${analyst.email})`
			} else {
				name = analyst.email
			}
			const a = {
				calendarUserType: 'INDIVIDUAL',
				commonName: analyst.name,
				email: analyst.email,
				isUser: true,
				avatar: analyst.uid,
				dropdownName: name,
				userId: analyst.uid,
			}
			this.matches.push(a)
		})
	},
	methods: {
		// Method to execute the Search for each change in input
		findAnalysts: debounce(async function(query) {
			this.isLoading = true
			const matches = []
			if (query.length > 0) {
				matches.push(...this.findAnalystsFromAPI(query))
				// Source of the Regex: https://stackoverflow.com/a/46181
				// eslint-disable-next-line
				const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
				if (emailRegex.test(query)) {
					const alreadyInList = matches.find((analyst) => analyst.email === query)
					if (!alreadyInList) {
						matches.unshift({
							calendarUserType: 'INDIVIDUAL',
							commonName: query,
							email: query,
							isUser: false,
							avatar: null,
							dropdownName: query,
							userId: '',
						})
					}
				}

				this.isLoading = false
				this.inputGiven = true
			} else {
				this.inputGiven = false
				this.isLoading = false
				matches.push(...this.allAnalysts)
			}

			this.matches = matches
		}, 500),
		addAnalyst(selectedValue) {
			this.$emit('addAnalyst', selectedValue)
		},
		findAnalystsFromAPI(query) {
			const data = this.allAnalysts.filter((analyst) => {
				query = query.toLowerCase()
				const lemail = analyst.email.toLowerCase()
				const lname = analyst.commonName.toLowerCase()
				return lemail.includes(query) || lname.includes(query)
			})
			console.log(data)
			return data.filter((analyst) => {
				return !this.alreadyInvitedEmails.includes(analyst.email)
			})
		},
	},
}
</script>
