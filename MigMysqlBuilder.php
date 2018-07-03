<?php 

//TODO: indexes, table params (engine etc.)., MigManager,  test generated sql, insert,update,delete: <row id="1" name="asasa"/>, $config, change positions...
// getPrimaryKeyColumnName($table) - always ID

require 'MigSqlBuilder.php';

class MigMysqlBuilder extends MigSqlBuilder
{

	//php 'array key not exists' NOTICE thing...
	protected function array_get($array, $key)
	{
		return isset($array[$key])? $array[$key] : null;
	}

	protected function quote($str)
	{
		return "`".$str."`";
	}

	protected function escape($str, $type = 'string')
	{
		if (!$str or is_numeric($str)) return $str;
		return addslashes($str);
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

	function rawQuery($sql)
	{
		$this->sql[] = $sql;
	}

  function dropTable($tableName)
  {
  	$this->sql[] = "DROP TABLE `$tableName`";
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

 	function createTable($tableName, $def)
 	{
 		foreach ($def as $name => $col) {
 			$sql[] = $this->getColumnDefStr($name, $col);
 		}

 		$pk = $this->getPrimaryKeyColumnName($table);
 		$sql[] = "PRIMARY KEY (`$pk`)";

 		$this->sql[] = "CREATE TABLE `$tableName` (".implode(',', $sql).")";

 	}

	function delete($table, $id)
	{
		$pk = $this->getPrimaryKeyColumnName($table);
		$this->sql[] = "DELETE FROM `$table` WHERE `$pk`=$id";
	}

	function insert($table, array $row)
	{		
		$sep = '';
		foreach($row as $k => $v) {
			$kstr .= $sep.$this->quote($k);
			if (is_null($v)) $vstr .= $sep."NULL";
			else $vstr .= $sep."'".$this->escape($v)."'";
			$sep = ',';
		}

		$this->sql[] = "INSERT INTO `$table` ($kstr) VALUES ($vstr)";
	}

	function update($table, $id, array $row)
	{
		$sep = '';
		foreach($row as $k => $v) {
			if (is_null($v)) $v = 'NULL'; else $v = "'".$this->escape($v)."'";
			$fields .= $sep.$this->quote($k)."=$v";
			$sep = ',';
		}

		$this->sql[] = "UPDATE `$table` set $fields WHERE ID='$id'";
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