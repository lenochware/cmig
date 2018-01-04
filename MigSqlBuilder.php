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

  abstract function createTable($tableName, $def);
  abstract function dropTable($tableName);
	abstract function renameTable($oldName, $newName);
	abstract function addColumn($table, $name, $def);
	abstract function dropColumn($table, $name);
	abstract function alterColumn($table, $name, $def);
	abstract function renameColumn($table, $oldName, $newName, array $def = []);

	//primary key in config? ID by default
	abstract function delete($table, $id);
	abstract function insert($table, array $row);
	abstract function update($table, $id, array $row);

	abstract function rawQuery($sql);

	function getSql()
	{
		return $this->sql;
	}
}

 ?>