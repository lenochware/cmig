<?php 

//TODO: indexes, table params (engine etc.)., MigManager,  test generated sql, insert,update,delete: <row id="1" name="asasa"/>, $config, change positions...

require 'MigSqlBuilder.php';

class MigMysqlBuilder extends MigSqlBuilder
{

	//php 'array key not exists' NOTICE thing...
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

	protected function getPrimaryKeyColumnName($table)
	{
		return 'ID';
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
		$this->sql[] = "ALTER TABLE `$table` ADD COLUMN ".$this->getColumnDefStr($name, $def);
	}

	function alterColumn($table, $name, $def)
	{
		$this->sql[] = "ALTER TABLE `$table` MODIFY COLUMN ".$this->getColumnDefStr($name, $def);
	}

 	function createTable($name, $def)
 	{
 		foreach ($def as $name => $col) {
 			$sql[] = $this->getColumnDefStr($name, $col);
 		}

 		$this->sql[] = "CREATE TABLE `$name` (".implode(',', $sql).")";

 	}

	function delete($table, array $aid)
	{
		$pk = $this->getPrimaryKeyColumnName($table);

		$sid = implode(',', $aid);
		$this->sql[] = "DELETE FROM `$table` WHERE `$pk` in ($sid)";
	}

	//@TODO
	function insert($table, array $rows)
	{
		/*

		$sep = '';
		foreach($data as $k => $v) {
			$kstr .= $sep.$this->drv->quote($k);
			if (is_null($v)) $vstr .= $sep."NULL";
			else $vstr .= $sep."'".$this->escape($v)."'";
			$sep = ',';
		}
		*/

		$this->sql[] = "INSERT INTO `$table` ($kstr) VALUES ($vstr)";
	}

	//@TODO
	function update($table, array $rows)
	{
		/*
		$sep = '';
		foreach($data as $k => $v) {
			//if ($k == '' or $v == '') continue;
			if (is_null($v)) $v = 'NULL'; else $v = "'".$this->escape($v)."'";
			$fields .= $sep.$this->drv->quote($k)."=$v";
			$sep = ',';
		}
		*/
		$this->sql[] = "UPDATE `$table` set $fields WHERE $where";
	}


	function renameTable($oldName, $newName)
	{
		$this->sql[] = "RENAME TABLE `$oldName` TO `$newName`";
	}

	function renameColumn($table, $oldName, $newName, array $def = [])
	{
		if (!$def) throw new Exception('Mysql requires column definition for renaming.');

		$this->sql[] = "ALTER TABLE `$table` CHANGE COLUMN `$oldName` ".$this->getColumnDefStr($newName, $def);
	}

}

 ?>