<!--
  - @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @copyright Copyright (c) 2023. Kevin Küchler <kevin.kuechler@csoc.de>
  -
  - @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
  - @author Kevin Küchler <kevin.kuechler@csoc.de>
  -->

<template>
	<div>
		<ArchiveTopBar />

		<ArchiveContent />
	</div>
</template>

<script>
import ArchiveTopBar from '../components/Archive/ArchiveTopBar.vue'
import ArchiveContent from '../components/Archive/ArchiveContent.vue'

export default {
	name: 'Archive',
	components: {
		ArchiveTopBar,
		ArchiveContent,
	},
	data() {
		return {
		}
	},
	methods: {
	},
	computed: {
	},
}
</script>
