<?php 

/**
 * It represents dump of the database.
 * Provides methods for load/save dump as xml file.
 * print_r $dump->data();
 */
class MigDump
{
	protected $data;

	function __construct($fileName = '')
	{
		if ($fileName) {
			$this->load($fileName);
		}
	}

	//get set data
	function data(array $data)
	{
		if ($data) {
			$this->data = $data;
		}

		return $this->data;
	}

	function parseXml($xmlString) {}

	function getXml(array $data) {}

	function load($fileName, $format = 'xml')
	{
		$s = file_get_contents($fileName);
		$this->data = $this->parseXml($s);
	}

	function save($fileName, $format = 'xml')
	{
		$s = $this->getXml($this->data);
		file_put_contents($fileName, $s);
	}
}

/**
 * Create database dump according configuration $config.
 * Database dump is represented as class MigDump.
 * $dump = $dumper->getDump();
 */
class MigDumper
{
	protected $config;
	protected $pdo;
	protected $databaseName;

	function __construct(PDO $pdo, array $config)
	{
		$this->pdo = $pdo;
		$this->setConfig($config);
		$this->databaseName = $this->pdo->query('select database()')->fetchColumn();
	}

	function setConfig(array $config)
	{
		$this->config = $config;
	}

	protected function getColumns($table)
	{
			return $this->pdo->query(
				"select * FROM INFORMATION_SCHEMA.COLUMNS
				WHERE table_name = '$table'
				AND TABLE_SCHEMA='$this->databaseName'")
			->fetchAll();
	}

	protected function getTables()
	{
			return $this->pdo->query(
				"select table_name FROM information_schema.tables where table_schema='$this->databaseName'")
			->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	function getDump()
	{
		var_dump($this->getTables());
		var_dump($this->getColumns('books'));

		$dump = new MigDump();
		return $dump;
	}
}

/**
 * Create diff of two database dumps as php migration script.
 * Database dump is represented as class MigDump.
 * $phpMigration = $migDiff->createPhpMigration($dump1, $dump2);
 */
class MigDiff
{
	function createPhpMigration(MigDump $a, MigDump $b)
	{
		$diff = $this->diff($a, $b);
		$s = $this->buildPhp($diff);
		return $s;
	}
}

class MigTable
{
	protected $sql;
	protected $name;

	function __construct($name)
	{
		$this->name = $name;
	}

	function addColumn($name, $definition)
	{
		return $this;
	}

	function dropColumn($name)
	{
		return $this;
	}

	function alterColumn($name, $definition)
	{
		return $this;
	}

	function addRow(array $data)
	{
		return $this;
	}

	function dropRow($id)
	{
		return $this;
	}

	function updateRow($id, array $data)
	{
		return $this;
	}

	function getSql()
	{

	}

}

/**
 * Provides interface for creating/altering database schema and data.
 * Translate php commands to sql statements for supported database.
 * $builder->dropTable('test');  $builder->createTable(...) ...
 * $sql = $builder->getSql();
 */
class MigSqlBuilder
{
	protected $tables;

	function getSql()
	{
		$sql = '';
		foreach ($this->tables as $table) {
			$sql .= $table->getSql();
		}

		return $sql;
	}

	function getTable($name)
	{
		if (!$this->tables[$name]) {
			$this->tables[$name] = new MigTable($name);
		}
		return $this->tables[$name];
	}

	function createTable($name, $func)
	{
		call_user_func($func, $this->getTable($name));
		return $this;
	}

	function dropTable($name)
	{
		return $this;
	}

	function alterTable($name, $func)
	{
		call_user_func($func, $this->getTable($name));
		return $this;
	}

	function updateTable($name, $func)
	{
		call_user_func($func, $this->getTable($name));
		return $this;
	}

}


 ?>