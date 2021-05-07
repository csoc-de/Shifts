(function ($, OC) {
	var skillGroupList = []
	$(document).ready(function() {
		OCA.Shifts = _.extend({}, OCA.Shifts)
		if (!OCA.Shifts.AppName) {
			OCA.Shifts = {
				AppName: 'shifts'
			}
		}
		skillGroupList = jQuery.parseJSON($('#skillGroupList').text())

		for (const skillGroup of skillGroupList) {
			$('#skillGroups').append(
				'<div class="skillGroup" style="display: flex">' +
				`<input id="${skillGroup.id}" class="skillGroupInput" value="${skillGroup.name}" placeholder="" type="text" />` +
				`<div id="${skillGroup.id}" class="theme-remove-bg icon icon-delete deleteButton" data-toggle="tooltip" data-original-title="Löschen"></div>` +
				'</div>'
			)
		}

	})

	$('#addNewSkillGroup').click(function () {
		const id = Math.max(...skillGroupList.map(skillGroup => skillGroup.id)) + 1
		$('#skillGroups').append(
			'<div class="skillGroup" style="display: flex">' +
			`<input id="${id}" class="skillGroupInput" value="" placeholder="" type="text" />` +
			`<div id="${id}" class="theme-remove-bg icon icon-delete deleteButton" data-toggle="tooltip" data-original-title="Löschen"></div>` +
			'</div>'
		)
	})

	$('body').on('click', 'div.deleteButton', function() {
		const id = $(this).attr('id')

		$(`#${id}`).parents('.skillGroup').remove()
	})

	$('#saveButton').click(function () {
		var shiftsCalendarName = ($("#shiftsCalendarName").val() || "").trim()
		var shiftsOrganizerName = ($("#shiftsOrganizerName").val() || "").trim()
		var shiftsOrganizerEmail = ($("#shiftsOrganizerEmail").val() || "").trim()
		var shiftsAdminGroup = ($("#shiftsAdminGroup").val() || "").trim()
		var shiftsWorkerGroup = ($("#shiftsWorkerGroup").val() || "").trim()

		var skillGroups = $("input[class='skillGroupInput']")
								.map(function(){
									return {
										id: $(this).attr('id'),
										name: $(this).val(),
									}
								}).get()

		var data = {
			calendarName: shiftsCalendarName,
			organizerName: shiftsOrganizerName,
			organizerEmail: shiftsOrganizerEmail,
			adminGroup: shiftsAdminGroup,
			shiftWorkerGroup: shiftsWorkerGroup,
			skillGroups: skillGroups,
		}

		$.ajax({
			method: "PUT",
			url: OC.generateUrl("apps/shifts/settings"),
			data,
			success: function onSuccess(response) {
				console.log(response)
				if (response && (response.documentserver != null)) {
					if (response.error) {
						OCP.Toast.error(t(OCA.Shifts.AppName, "Error when trying to connect"))
					} else {
						OCP.Toast.success(t(OCA.Shifts.AppName, "Success"))
					}
				}
			}
		})
	})

})(jQuery, OC)
