<?php 

/**
 * Provides interface for creating/altering database schema and data.
 * Translate php commands to sql statements for supported database.
 * $builder->dropTable('test');  $builder->createTable(...) ...
 * $sql = $builder->getSql();
 */
abstract class MigSqlBuilder
{
	protected $sql;

  abstract function createTable($name, $def);
  abstract function dropTable($name);
	abstract function updateTable($name, $data);
	abstract function addColumn($table, $name, $def);
	abstract function dropColumn($table, $name);
	abstract function changeColumn($table, $name, $def);

	function getSql()
	{
		return $this->sql;
	}
}

 ?>