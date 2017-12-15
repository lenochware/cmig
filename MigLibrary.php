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

	function __construct(array $config)
	{
		$this->setConfig($config);
	}

	function setConfig(array $config)
	{
		$this->config = $config;
	}

	function getDump()
	{
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

/**
 * Provides interface for creating/altering database schema and data.
 * Translate php commands to sql statements for supported database.
 * $builder->dropTable('test');  $builder->createTable(...) ...
 * $sql = $builder->getSql();
 */
class MigSqlBuilder
{
	protected $sql = [];
	protected $currentTable;

	function getSql()
	{
		return $this->sql;
	}

	function createTable($name, $func)
	{
		$this->currentTable = $name;
		call_user_func($func, $this);
		$this->currentTable = null;
		return $this;
	}

	function dropTable($name)
	{
		return $this;
	}

	function alterTable($name, $func)
	{
		return $this;
	}

	function updateTable($name, $func)
	{
		return $this;
	}

	function addColumn($name)
	{
		return $this;
	}

	function dropColumn($name)
	{
		return $this;
	}

	function alterColumn($name)
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

}


 ?>