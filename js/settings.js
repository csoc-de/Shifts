(function ($, OC) {
	var categoryList = []
	$(document).ready(function() {
		OCA.Shifts = _.extend({}, OCA.Shifts)
		if (!OCA.Shifts.AppName) {
			OCA.Shifts = {
				AppName: 'shifts'
			}
		}
		categoryList = jQuery.parseJSON($('#categoriesList').text())

		for (const category of categoryList) {
			$('#shiftsWorkerCategories').append(
				'<div class="shiftsWorkerCategory" style="display: flex">' +
				'<p>' +
				`<input id="${category.id}" class="shiftsWorkerCategoriesInput" value="${category.name}" placeholder="" type="text" />` +
				'</p>' +
				`<div id="${category.id}" class="theme-remove-bg icon icon-delete deleteButton" data-toggle="tooltip" data-original-title="Löschen"></div>` +
				'</div>'
			)
		}

	})

	$('#addNewCategory').click(function () {
		const id = Math.max(...categoryList.map(category => category.id)) + 1
		$('#shiftsWorkerCategories').append(
			'<div class="shiftsWorkerCategory" style="display: flex">' +
			'<p>' +
			'<input id="${id}" class="shiftsWorkerCategoriesInput" value="" placeholder="" type="text" />' +
			'</p>' +
			`<div id="${id}" class="theme-remove-bg icon icon-delete deleteButton" data-toggle="tooltip" data-original-title="Löschen"></div>` +
			'</div>'
		)
	})

	$('body').on('click', 'div.deleteButton', function() {
		const id = $(this).attr('id')

		$(`#${id}`).parents('.shiftsWorkerCategory').remove()
	})

	$('#saveButton').click(function () {
		console.log('save')

		var shiftsCalendarName = ($("#shiftsCalendarName").val() || "").trim()
		var shiftsOrganizerName = ($("#shiftsOrganizerName").val() || "").trim()
		var shiftsOrganizerEmail = ($("#shiftsOrganizerEmail").val() || "").trim()
		var shiftsAdminGroup = ($("#shiftsAdminGroup").val() || "").trim()
		var shiftsWorkerGroup = ($("#shiftsWorkerGroup").val() || "").trim()

		var shiftsCategories = $("input[class='shiftsWorkerCategoriesInput']")
								.map(function(){
									console.log($(this).attr('id'))
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
			shiftWorkerCategories: shiftsCategories
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
