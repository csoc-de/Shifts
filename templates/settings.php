<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */
style('shifts', 'settings');
script('shifts', 'settings');
?>

<div id="admin_settings">
	<h2><?php p($l->t('Shifts')); ?></h2>

	<p><?php p($l->t('Name of the Calendar to save Events to'))?></p>
	<p>
		<input id="shiftsCalendarName" value="<?php p($_['calendarName']) ?>" placeholder="Calendar" type="text" />
	</p>

	<p><?php p($l->t('Name of the Shifts Organizer'))?></p>
	<p>
		<input id="shiftsOrganizerName" value="<?php p($_['organizerName']) ?>" placeholder="admin" type="text" />
	</p>

	<p><?php p($l->t('Email of the Shifts Organizer'))?></p>
	<p>
		<input id="shiftsOrganizerEmail" value="<?php p($_['organizerEmail']) ?>" placeholder="technik@csoc.de" type="text" />
	</p>

	<p><?php p($l->t('Shifts Admin Group Name'))?></p>
	<p>
		<input id="shiftsAdminGroup" value="<?php p($_['adminGroup']) ?>" placeholder="ShiftsAdmin" type="text" />
	</p>

	<p><?php p($l->t('Shifts Worker Group Name'))?></p>
	<p>
		<input id="shiftsWorkerGroup" value="<?php p($_['shiftWorkerGroup']) ?>" placeholder="Blueteam" type="text" />
	</p>
	<div id="shiftsWorkerCategoriesContainer">
		<?php
		foreach ($_['shiftsWorkerCategories'] as $category => $data) {
			?>
			<p><?php p($l->t($category))?></p>
			<p>
				<input class="shiftsWorkerCategories" value="<?php p($_['shiftsWorkerCategories']) ?>" placeholder="" type="text" />
			</p>
			<?php
		}
		?>
	</div>
	<button id="addNewCategory"><?php p($l->t('Add Category')); ?></button>

	<button><?php p($l->t('Save')); ?></button>
</div>
