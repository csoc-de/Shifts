<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */
style('shifts', 'settings');
script('shifts', 'settings');
?>

<div id="admin_settings">
	<h2><?php p($l->t('Shifts')); ?> <a class="icon-info svg" title href="https://gitlab.csoc.de/csoc/nextcloud/shifts" data-original-title="<?php p($l->t('Dokumentation')); ?>"></a></h2>

	<p><?php p($l->t('Name des Schichtkalenders'))?></p>
	<p>
		<input id="shiftsCalendarName" value="<?php p($_['calendarName']) ?>" placeholder="Calendar" type="text" />
	</p>

	<p><?php p($l->t('Name des Schichtorganisators'))?></p>
	<p>
		<input id="shiftsOrganizerName" value="<?php p($_['organizerName']) ?>" placeholder="admin" type="text" />
	</p>

	<p><?php p($l->t('Email des Schichtorganisators'))?></p>
	<p>
		<input id="shiftsOrganizerEmail" value="<?php p($_['organizerEmail']) ?>" placeholder="technik@csoc.de" type="text" />
	</p>

	<p><?php p($l->t('Name der Schichtadmin-Gruppe'))?></p>
	<p>
		<input id="shiftsAdminGroup" value="<?php p($_['adminGroup']) ?>" placeholder="ShiftsAdmin" type="text" />
	</p>

	<p><?php p($l->t('Name der Schichtmitarbeiter-Gruppe'))?></p>
	<p>
		<input id="shiftsWorkerGroup" value="<?php p($_['shiftWorkerGroup']) ?>" placeholder="Blueteam" type="text" />
	</p>
	<div id="shiftsWorkerCategoriesContainer">
		<p><?php p($l->t('Name der Mitarbeiter-Skill Gruppen'))?></p>

		<p id="categoriesList" style="visibility: hidden; width: 0; height: 0"><?php echo( json_decode($_['shiftWorkerCategories']))?></p>
		<div id="shiftsWorkerCategories">
			<?php
			foreach ($_['shiftWorkerCategories'] as $category => $data) {
				?>
				<div style="display: flex">
					<p>
						<input class="shiftsWorkerCategoriesInput" value="<?php p($data) ?>" placeholder="" type="text" />
					</p>
					<div id="<?php p($category)?>" class="theme-remove-bg icon icon-delete deleteButton" data-toggle="tooltip" data-original-title="Löschen"></div>
				</div>
				<?php
			}
			?>
		</div>
		<button id="addNewCategory">
			<?php p($l->t('Hinzufügen')); ?>
		</button>
	</div>



	<button><?php p($l->t('Speichern')); ?></button>
</div>
