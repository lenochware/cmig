<?php 

require "MigDumper.php";

class MigMysqlDumper extends MigDumper
{
	protected function getDatabaseName()
	{
		return $this->pdo->query('select database()')->fetchColumn();
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

	protected function getTables()
	{
			return $this->pdo->query(
				"select table_name FROM information_schema.tables where table_schema='$this->databaseName'")
			->fetchAll(PDO::FETCH_COLUMN, 0);
	}
}

 ?>