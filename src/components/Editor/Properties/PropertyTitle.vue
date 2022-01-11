<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  -->
<!--
  - Simple Component to Input a title of a Shift-Type
  -->
<template>
	<div class="property-title">
		<div class="property-title__input">
			<input type="text"
				:placeholder="$t('shifts', 'Name of the Shift-Type')"
				:value="value"
				@input.prevent.stop="changeValue">
		</div>
	</div>
</template>

<script>
export default {
	name: 'PropertyTitle',
	props: {
		value: {
			type: String,
			default: '',
		},
	},
	methods: {
		changeValue(event) {
			this.$emit('update:value', event.target.value)
		},
	},
}
</script>
