
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

(function($, OC) {
	let skillGroupList = []
	$(document).ready(function() {
		OCA.Shifts = _.extend({}, OCA.Shifts)
		if (!OCA.Shifts.AppName) {
			OCA.Shifts = {
				AppName: 'shifts',
			}
		}
		skillGroupList = jQuery.parseJSON($('#skillGroupList').text())

		for (const skillGroup of skillGroupList) {
			$('#skillGroups').append(
				'<div class="skillGroup" style="display: flex">'
				+ `<input id="${skillGroup.id}" class="skillGroupInput" value="${skillGroup.name}" placeholder="" type="text" />`
				+ `<div id="${skillGroup.id}" class="theme-remove-bg icon icon-delete deleteButton" data-toggle="tooltip" data-original-title="Löschen"></div>`
				+ '</div>'
			)
		}

	})

	$('#addNewSkillGroup').click(function() {
		const id = Math.max(...skillGroupList.map(skillGroup => skillGroup.id)) + 1
		$('#skillGroups').append(
			'<div class="skillGroup" style="display: flex">'
			+ `<input id="${id}" class="skillGroupInput" value="" placeholder="" type="text" />`
			+ `<div id="${id}" class="theme-remove-bg icon icon-delete deleteButton" data-toggle="tooltip" data-original-title="Löschen"></div>`
			+ '</div>'
		)
	})

	$('body').on('click', 'div.deleteButton', function() {
		const id = $(this).attr('id')

		$(`#${id}`).parents('.skillGroup').remove()
	})

	$('#saveButton').click(function() {
		const shiftsCalendarName = ($('#shiftsCalendarName').val() || '').trim()
		const shiftsOrganizerName = ($('#shiftsOrganizerName').val() || '').trim()
		const shiftsOrganizerEmail = ($('#shiftsOrganizerEmail').val() || '').trim()
		const shiftsAdminGroup = ($('#shiftsAdminGroup').val() || '').trim()
		const shiftsWorkerGroup = ($('#shiftsWorkerGroup').val() || '').trim()

		const skillGroups = $("input[class='skillGroupInput']")
			.map(function() {
				return {
					id: $(this).attr('id'),
					name: $(this).val(),
				}
			}).get()

		const data = {
			calendarName: shiftsCalendarName,
			organizerName: shiftsOrganizerName,
			organizerEmail: shiftsOrganizerEmail,
			adminGroup: shiftsAdminGroup,
			shiftWorkerGroup: shiftsWorkerGroup,
			skillGroups,
		}
		OC.msg.startSaving('#OWMApiKeySettingsMsg');
		$.ajax({
			method: 'PUT',
			url: OC.generateUrl('apps/shifts/settings'),
			data,
			success: function onSuccess(response) {
				if (response.error || response.error === undefined) {
					$('.toast-header').text(t('shifts', 'Successfully saved changes'));
					$('.toast').show(1).delay(1000).hide(1);
					console.log('Successfull saving of settings')
				} else {
					$('.toast-header').text(t('shifts', 'Could not save changes'));
					$('.toast').show(1).delay(1000).hide(1);
					console.log('Error: ', response.error)
				}
			},
		})
	})

})(jQuery, OC)
