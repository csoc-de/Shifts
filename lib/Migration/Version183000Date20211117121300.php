<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@csoc.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@csoc.de>
 */

declare(strict_types=1);

namespace OCA\Shifts\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version183000Date20211117121300 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		$schema = $schemaClosure();

		if (!$schema->hasTable('shifts_cal_changes')) {
			$table = $schema->createTable('shifts_cal_changes');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('shift_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('shift_type_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('shift_date', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('old_user_id', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('new_user_id', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('action', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('date_changed', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('admin_id', 'string', [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('is_done', 'boolean', [
				'notnull' => false,
				'default' => false,
			]);

			$table->setPrimaryKey(['id']);
		}
		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
