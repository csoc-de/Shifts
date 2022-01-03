<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

/** @var $l \OCP\IL10N */
/** @var $_ array */
style('shifts', 'settings');
script('shifts', 'settings');
?>

<div id="admin_settings">
	<div class="toast" style="display:none;">
		<div class="toast-header">
			Toast Header
		</div>
	</div>

	<h2><?php p($l->t('Shifts')); ?>
		<a class="icon-info svg" title href="https://github.com/csoc-de/Shifts"
		   data-original-title="<?php p($l->t('Documentation')); ?>">
		</a>
	</h2>
	<div class="settings_container">
		<label for="shiftsCalendarName"><?php p($l->t('Name of the Shiftscalendar'))?></label>
		<input id="shiftsCalendarName" value="<?php p($_['calendarName']) ?>" placeholder="ShiftsCalendar" type="text" />
	</div>
	<div class="settings_container">
		<label for="shiftsOrganizerName"><?php p($l->t('Name of the Shiftsorganizer'))?></label>
		<input id="shiftsOrganizerName" value="<?php p($_['organizerName']) ?>" placeholder="admin" type="text" />
	</div>
	<div class="settings_container">
		<label for="shiftsOrganizerEmail"><?php p($l->t('Email fo the Shiftsorganizer'))?></label>
		<input id="shiftsOrganizerEmail" value="<?php p($_['organizerEmail']) ?>" placeholder="admin@test.com" type="text" />
	</div>
	<div class="settings_container">
		<label for="shiftsAdminGroup"><?php p($l->t('Name of the Shiftsadmin Group'))?></label>
		<input id="shiftsAdminGroup" value="<?php p($_['adminGroup']) ?>" placeholder="ShiftsAdmin" type="text" />
	</div>
	<div class="settings_container">
		<label for="shiftsWorkerGroup"><?php p($l->t('Name of the Analyst Group'))?></label>
		<input id="shiftsWorkerGroup" value="<?php p($_['shiftWorkerGroup']) ?>" placeholder="Analyst" type="text" />
	</div>
	<div id="skillGroupsContainer" class="settings_container">
		<p><?php p($l->t('Name of the Analyst-Skill Group'))?></p>

		<p id="skillGroupList" style="visibility: hidden; width: 0; height: 0"><?php echo( json_encode($_['skillGroups']))?></p>
		<div id="skillGroups">
		</div>
		<button id="addNewSkillGroup">
			<?php p($l->t('Add')); ?>
		</button>
	</div>



	<button id="saveButton"><?php p($l->t('Save')); ?></button>
</div>
