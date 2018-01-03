<?php 

/**
 * Create diff of two database dumps as php migration script.
 * Database dump is represented as class MigDump.
 * $phpMigration = $migDiff->createPhpMigration($dump1, $dump2);
 */
class MigDiff
{
	protected $a;
	protected $b;

	function __construct(MigDump $a, MigDump $b)
	{
		$this->a = $a;
		$this->b = $b;
	}

	function createPhpMigration()
	{
		$up = $this->buildPhp($this->compare($this->a, $this->b));
		$down = $this->buildPhp($this->compare($this->b, $this->a));

		$trans = [
			'{MIGRATION_ID}' => date('Ymd_His'),
			'{MIGRATION_UP_CODE}' => "\t\t".str_replace("\n", "\n\t\t", $up),
			'{MIGRATION_DOWN_CODE}' => "\t\t".str_replace("\n", "\n\t\t", $down),
		];

		return strtr(file_get_contents(__DIR__.'/Migration.tpl'), $trans);
	}

	protected function compareTables($tableName, $a, $b)
	{
		$diff = [];

		$ka = array_keys($a['columns']);
		$kb = array_keys($b['columns']);

		$columns = array_unique(array_merge($ka, $kb));

		foreach ($columns as $col) {
			if (in_array($col, $ka) and !in_array($col, $kb)) {
				$diff[] = ['command' => 'dropColumn', 'table' => $tableName, 'name' => $col];
			}
			elseif(in_array($col, $kb) and !in_array($col, $ka)) {
				$diff[] = ['command'=> 'addColumn', 'table' => $tableName, 'name' => $col, 'attrib' => $b['columns'][$col]];
			}
			else {
				$diffAttrib = array_diff_assoc($b['columns'][$col], $a['columns'][$col]);
				if ($diffAttrib) {
					$diff[] = ['command'=> 'changeColumn', 'table' => $tableName, 'name' => $col, 'attrib' => $diffAttrib];					
				}
			}
		}

		$ka = array_keys((array)$a['rows']);
		$kb = array_keys((array)$b['rows']);

		$rows = array_unique(array_merge($ka, $kb));

		foreach ($rows as $rowId) {
			if (in_array($rowId, $ka) and !in_array($rowId, $kb)) {
				$diff[] = ['command' => 'delete', 'table' => $tableName, 'id' => $rowId];
			}
			elseif(in_array($rowId, $kb) and !in_array($rowId, $ka)) {
				$diff[] = ['command'=> 'insert', 'table' => $tableName, 'attrib' => $b['rows'][$rowId]];
			}
			else {
				$diffAttrib = array_diff_assoc($b['rows'][$rowId], $a['rows'][$rowId]);
				if ($diffAttrib) {
					$diff[] = ['command'=> 'update', 'table' => $tableName, 'id' => $rowId, 'attrib' => $diffAttrib];					
				}
			}
		}

		return $diff;
	}

	function compare()
	{
		$diff = [];

		$a = $this->a->data();
		$b = $this->b->data();

		$ka = array_keys($a);
		$kb = array_keys($b);

		$tables = array_unique(array_merge($ka, $kb));

		foreach ($tables as $t) {
			if (in_array($t, $ka) and !in_array($t, $kb)) {
				$diff[] = ['command' => 'dropTable', 'name' => $t];
			}
			elseif(in_array($t, $kb) and !in_array($t, $ka)) {
				$diff[] = ['command'=> 'createTable', 'name' => $t, 'columns' => $b[$t]['columns']];
			}
			else {
				$diff = array_merge($diff, $this->compareTables($t, $a[$t], $b[$t]));
			}
		}

		return $diff;
	}

	function buildPhp(array $diff)
	{
		$s = '';

		foreach ($diff as $diffRow) {

			$command = $diffRow['command'];
			unset($diffRow['command']);

			$par = [];
			foreach ($diffRow as $key => $value) {
				$par[] = is_array($value)? var_export($value, true) : "'$value'";
			}

			$s .= "\$builder->$command(".implode(', ', $par).");\r\n\r\n";
		}

		return trim($s);
	}
}

 ?>