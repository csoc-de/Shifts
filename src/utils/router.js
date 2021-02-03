import { loadState } from '@nextcloud/initial-state'

export function getInitialView() {
	try {
		return loadState('shifts', 'initial_view')
	} catch (error) {
		return ''
	}
}
