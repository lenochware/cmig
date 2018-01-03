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
	abstract function renameTable($oldName, $newName);
	abstract function addColumn($table, $name, $def);
	abstract function dropColumn($table, $name);
	abstract function alterColumn($table, $name, $def);
	abstract function renameColumn($table, $oldName, $newName, array $def = []);

	//primary key in config? ID by default
	abstract function delete($table, array $aid);
	abstract function insert($table, array $rows);
	abstract function update($table, $id, array $rows);

	function getSql()
	{
		return $this->sql;
	}
}

 ?>