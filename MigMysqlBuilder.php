<?php 

require 'MigSqlBuilder.php';

class MigMysqlBuilder extends MigSqlBuilder
{
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
		$sql[] = "ALTER TABLE `$table` ADD `$name`";
		$sql[] = $def['type'];
		if ($def['extra']) $sql[] = $def['extra'];
		$sql[] = "default ". ($def['default'] ?: 'NULL');

		$this->sql[] = implode(' ', $sql);
	}

	function changeColumn($table, $name, $def) {

	}

 	function createTable($name, $def) {

 	}

	function updateTable($name, $data)
	{

	}

}

 ?>