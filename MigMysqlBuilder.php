<?php 

require 'MigSqlBuilder.php';

class MigMysqlBuilder extends MigSqlBuilder
{

 	function createTable($name, $def) {

 	}

  function dropTable($name) {
  	$this->sql[] = "DROP TABLE `$name`";

  }

	function updateTable($name, $data) {

	}

	function addColumn($table, $name, $def) {

	}

	function dropColumn($table, $name) {

	}

	function changeColumn($table, $name, $def) {

	}

}

 ?>