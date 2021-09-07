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
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version100001Date20181013124731 extends SimpleMigrationStep {

	/**
	 * Creates Tables for Version 1.0.0 (deprecated)
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('shifts')) {
			$table = $schema->createTable('shifts');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('shift_type_id', 'integer', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('date', 'string', [
				'notnull' => true,
				'default' => ''
			]);

			$table->setPrimaryKey(['id']);
		}
		if (!$schema->hasTable('shifts_type')) {
			$table = $schema->createTable('shifts_type');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('desc', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('start_time_stamp', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('stop_time_stamp', 'string', [
				'notnull' => true,
				'length' => 200,
			]);

			$table->setPrimaryKey(['id']);
		}
		return $schema;
	}
}
