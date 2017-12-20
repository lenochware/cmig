<?php 

require "MigDump.php";

/**
 * Create database dump according configuration $config.
 * Database dump is represented as class MigDump.
 * $dump = $dumper->getDump();
 */
abstract class MigDumper
{
	protected $config;
	protected $pdo;
	protected $databaseName;

	function __construct(PDO $pdo, array $config)
	{
		$this->pdo = $pdo;
		$this->setConfig($config);
		$this->databaseName = $this->getDatabaseName();
	}

	function setConfig(array $config)
	{
		$this->config = $config;
	}

	protected abstract function getDatabaseName();
	protected abstract function getColumns($table);
	protected abstract function getIndexes($table);
	protected abstract function getTables();

	function getDump()
	{
		$data = [];
		foreach ($this->getTables() as $table) {
			$data[$table] = $this->getColumns($table);
		}

		$dump = new MigDump();
		$dump->data($data);
		return $dump;
	}
}

 ?>