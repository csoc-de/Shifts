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
  - Component to search for Analysts
  -->
<template>
	<div>
		<AnalystsListSearch
			v-if="!isReadOnly"
			:already-invited-emails="alreadyInvitedEmails"
			@add-analyst="addAnalyst" />
		<AnalystsListItem
			v-for="analyst in analysts"
			:key="analyst.email"
			:attendee="analyst"
			:is-read-only="isReadOnly"
			@remove-attendee="removeAnalyst" />
		<NoAnalystsView
			v-if="isReadOnly && isListEmpty" />
		<NoAnalystsView
			v-if="!isReadOnly && isListEmpty " />

		<!-- TODO FreeBusy -->
	</div>
</template>

<script>
import AnalystsListSearch from './AnalystsListSearch'
import AnalystsListItem from './AnalystsListItem'
import NoAnalystsView from './NoAnalystsView'
export default {
	name: 'AnalystsList',
	components: {
		NoAnalystsView,
		AnalystsListItem,
		AnalystsListSearch,
	},
	props: {
		isReadOnly: {
			type: Boolean,
			required: true,
		},
		newShiftInstance: {
			type: Object,
			required: true,
		},
	},
	computed: {
		// returns all Analysts
		analysts() {
			if (!this.newShiftInstance.organizer) {
				return this.newShiftInstance.analysts
			}

			return this.newShiftInstance.analysts
				.filter(analyst => analyst.uri !== this.newShiftInstance.organizer.uri)
		},
		// returns whether the Analysts-List is empty or not
		isListEmpty() {
			return this.newShiftInstance.organizer === null
				&& this.newShiftInstance.analysts.length === 0
		},
		// returns invited Emails as list
		alreadyInvitedEmails() {
			return this.newShiftInstance.analysts.map(analyst => {
				if (analyst.email.startsWith('mailto:')) {
					return analyst.email.substr(7)
				}

				return analyst.email
			})
		},
	},
	methods: {
		addAnalyst(analyst) {
			this.$store.commit('addAnalyst', analyst)
		},
		removeAnalyst(analyst) {
			this.$store.commit('removeAnalyst', analyst.userId)
		},
	},
}
</script>
