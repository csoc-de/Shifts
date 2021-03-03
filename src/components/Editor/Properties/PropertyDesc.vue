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

<template>
	<div v-if="display" class="property-text">
		<div
			class="property-text__input">
			<textarea
				v-autosize="true"
				:placeholder="placeholder"
				:rows="1"
				:title="readableName"
				:value="value"
				@input.prevent.stop="changeValue" />
		</div>
	</div>
</template>

<script>
import autosize from '../../../directives/autosize'
import { linkify } from '../../../directives/linkify'

export default {
	name: 'PropertyDesc',
	directives: {
		autosize,
		linkify,
	},
	props: {
		value: {
			type: String,
			default: '',
		},
		placeholder: {
			type: String,
			default: '',
		},
		readableName: {
			type: String,
			default: 'Description',
		},
		icon: {
			type: String,
			default: 'icon-menu',
		},
	},
	computed: {
		display() {
			return true
		},
	},
	methods: {
		changeValue(event) {
			if (event.target.value.trim() === '') {
				this.$emit('update:value', null)
			} else {
				this.$emit('update:value', event.target.value)
			}
		},
	},
}
</script>
