(function ($, OC) {
	$(document).ready(function () {
		OCA.Shifts = _.extend({}, OCA.Shifts);
		if (!OCA.Shifts.AppName) {
			OCA.Shifts = {
				AppName: 'Shifts'
			};
		}

		$("#addNewCategory").click(function () {
			$("#shiftsWorkerCategoriesContainer").append('<p>Test<p/>');
		})
	})
})
