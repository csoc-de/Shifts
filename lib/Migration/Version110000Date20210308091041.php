<?php

declare(strict_types=1);

namespace OCA\Shifts\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version110000Date20210308091041 extends SimpleMigrationStep {

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
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('shifts_change')) {
			$table = $schema->createTable('shifts_change');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('old_analyst_id', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('new_analyst_id', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('admin_approval', 'string', [
				'length' => 200,
			]);
			$table->addColumn('admin_approval_date', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('analyst_approval', 'string', [
				'length' => 200,
			]);
			$table->addColumn('analyst_approval_date', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('old_shifts_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('new_shifts_id', 'integer');
			$table->addColumn('desc', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('type', 'string', [
				'notnull' => true,
				'length' => 200,
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
