<?php 

require "MigDumper.php";

class MigMysqlDumper extends MigDumper
{
	protected function getDatabaseName()
	{
		return $this->pdo->query('select database()')->fetchColumn();
	}

	protected function getRows($table)
	{
		$rows = [];
		$stmt = $this->pdo->query("select * FROM `$table`", PDO::FETCH_ASSOC);
		foreach ($stmt as $row) {
			$rows[$row['ID']] = $row;
		}
		return $rows;
	}

	protected function getColumns($table)
	{
			$rawColumns = $this->pdo->query(
				"select * FROM INFORMATION_SCHEMA.COLUMNS
				WHERE table_name = '$table'
				AND TABLE_SCHEMA='$this->databaseName'")
			->fetchAll();

			$columns = [];

			foreach ($rawColumns as $c) {
				$name = (string)$c['COLUMN_NAME'];
				$columns[$name] = [
					'name'     => $name,
					'default'  => (string)$c['COLUMN_DEFAULT'],
					'nullable' => ($c['IS_NULLABLE'] == 'YES')? 'true' : '',
					'type'     => (string)$c['COLUMN_TYPE'],
					'length'   => (string)$c['CHARACTER_MAXIMUM_LENGTH'],
					'position' => (string)$c['ORDINAL_POSITION'],
					'extra'    => (string)$c['EXTRA'],
				];
			}

			return $columns;
	}

	function getIndexes($table)
	{
		$rawIndexes = $this->pdo->query(
		"select * FROM information_schema.statistics
		WHERE table_schema='$this->databaseName'
		AND table_name='$table'
		ORDER BY INDEX_NAME,SEQ_IN_INDEX")
		->fetchAll();

		$name = '';
		$indexes = array();
		foreach ($rawIndexes as $idx) {
			if ($idx['INDEX_NAME'] != $name) {
				$name = $idx['INDEX_NAME'];
				$indexes[$name] = array(
				'name' => $name,
				'type' => $idx['INDEX_TYPE'],
				'nullable' => ($idx['NULLABLE'] == 'YES')? 'true' : '',
				'unique' => ($idx['NON_UNIQUE'] != 1)? 'true' : '',
				);
			}

			$indexes[$name]['columns'][] = $idx['COLUMN_NAME'];
		}

		return $indexes;
	}

	function getExtras($table)
	{

		$rawExtras = $this->pdo->query(
			"select CCSA.character_set_name, T.* 
			FROM information_schema.`TABLES` T, 
			information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
			WHERE CCSA.collation_name = T.table_collation
			AND T.table_schema = '$this->databaseName'
			AND T.table_name = '$table'")->fetch();

		$extras = [
			'engine' => $rawExtras['ENGINE'],
			//'collation' => $rawExtras['TABLE_COLLATION'],
			'default_charset' => $rawExtras['character_set_name'],
		];

		return $extras;
	}

	protected function getTables()
	{
			return $this->pdo->query(
				"select table_name FROM information_schema.tables where table_schema='$this->databaseName'")
			->fetchAll(PDO::FETCH_COLUMN, 0);
	}
}

 ?>