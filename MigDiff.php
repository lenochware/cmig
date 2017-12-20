<?php 

/**
 * Create diff of two database dumps as php migration script.
 * Database dump is represented as class MigDump.
 * $phpMigration = $migDiff->createPhpMigration($dump1, $dump2);
 */
class MigDiff
{
	function createPhpMigration(MigDump $a, MigDump $b)
	{
		$diff = $this->compare($a, $b);
		$s = $this->buildPhp($diff);
		return $s;
	}

	protected function compareTables($tableName, $a, $b)
	{
		$diff = [];

		$ka = array_keys($a);
		$kb = array_keys($b);

		$columns = array_unique(array_merge($ka, $kb));

		foreach ($columns as $col) {
			if (in_array($col, $ka) and !in_array($col, $kb)) {
				$diff[] = ['command' => 'dropColumn', 'table' => $tableName, 'name' => $col];
			}
			elseif(in_array($col, $kb) and !in_array($col, $ka)) {
				$diff[] = ['command'=> 'addColumn', 'table' => $tableName, 'name' => $col, 'attrib' => $b[$col]];
			}
			else {
				$diffAttrib = array_diff_assoc($a[$col], $b[$col]);
				if ($diffAttrib) {
					$diff[] = ['command'=> 'changeColumn', 'table' => $tableName, 'name' => $col, 'attrib' => $diffAttrib];					
				}
			}
		}

		return $diff;
	}

	function compare(MigDump $dumpA, MigDump $dumpB)
	{
		$diff = [];

		$a = $dumpA->data();
		$b = $dumpB->data();

		$ka = array_keys($a);
		$kb = array_keys($b);

		$tables = array_unique(array_merge($ka, $kb));

		foreach ($tables as $t) {
			if (in_array($t, $ka) and !in_array($t, $kb)) {
				$diff[] = ['command' => 'dropTable', 'name' => $t];
			}
			elseif(in_array($t, $kb) and !in_array($t, $ka)) {
				$diff[] = ['command'=> 'createTable', 'name' => $t, 'columns' => $b[$t]];
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

		$trans = [
			'{MIGRATION_ID}' => '12345',
			'{MIGRATION_UP_CODE}' => "\t\t".str_replace("\n", "\n\t\t", $s),
			'{MIGRATION_DOWN_CODE}' => '',
		];

		return strtr(file_get_contents(__DIR__.'/Migration.tpl'), $trans);
	}
}

 ?>