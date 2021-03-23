<!--
  - @copyright Copyright (c) 2019 Georg Ehrke <oc.list@georgehrke.com>
  -
  - @author Georg Ehrke <oc.list@georgehrke.com>
  -
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
  - Component to display selected Analysts in the AnalystsList
  -->
<template>
	<div class="invitees-list-item">
		<Avatar v-if="attendee.isUser" :user="attendee.avatar" :display-name="attendee.dropdownName" />
		<div class="invitees-list-item__displayname">
			{{ commonName }}
		</div>
		<Actions>
			<ActionButton
				icon="icon-delete"
				@click="removeAttendee">
				{{ $t('shifts', 'Remove attendee') }}
			</ActionButton>
		</Actions>
	</div>
</template>

<script>
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
export default {
	name: 'AnalystsListItem',
	components: {
		Avatar,
		ActionButton,
		Actions,
	},
	props: {
		attendee: {
			type: Object,
			required: true,
		},
		isReadOnly: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		// returns readable Displayname
		commonName() {
			if (this.attendee.commonName) {
				return this.attendee.commonName
			}
			if (this.attendee.uri && this.attendee.uri.startsWith('mailto:')) {
				return this.attendee.uri.substr(7)
			}
			return this.attendee.uri
		},
	},
	methods: {
		removeAttendee() {
			this.$emit('removeAttendee', this.attendee)
			this.isOpen = false
		},
	},
}
</script>
