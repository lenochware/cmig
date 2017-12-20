<?php 

//TODO: indexes, table params (engine etc.)., MigManager, renameTable, renameColumn, updateTable, $config

require 'MigSqlBuilder.php';

class MigMysqlBuilder extends MigSqlBuilder
{

	//php array key not exists NOTICE thing...
	protected function array_get($array, $key)
	{
		return isset($array[$key])? $array[$key] : null;
	}

	protected function getColumnDefStr($name, array $def)
	{
		$sql[] = "`$name`";
		if (isset($def['type'])) $sql[] = $def['type'];
		if (isset($def['nullable']) and !$def['nullable']) $sql[] = 'NOT NULL';

		if ($this->array_get($def, 'nullable') or $this->array_get($def, 'default')) {
			$sql[] = "default ". ($def['default'] ?: 'NULL');
		}

		if (isset($def['extra'])) $sql[] = $def['extra'];

		return implode(' ', $sql);
	}

  function dropTable($name)
  {
  	$this->sql[] = "DROP TABLE `$name`";
  }

	function dropColumn($table, $name)
	{
		$this->sql[] = "ALTER TABLE `$table` DROP COLUMN `$name`";
	}

	function addColumn($table, $name, $def)
	{
		$this->sql[] = "ALTER TABLE `$table` ADD ".$this->getColumnDefStr($name, $def);
	}

	function changeColumn($table, $name, $def) {

	}

 	function createTable($name, $def)
 	{
 		foreach ($def as $name => $col) {
 			$sql[] = $this->getColumnDefStr($name, $col);
 		}

 		$this->sql[] = "CREATE TABLE `$name` (".implode(',', $sql).")";

 	}

	function updateTable($name, $data)
	{

	}

}

 ?>