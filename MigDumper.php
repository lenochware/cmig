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
		if (!$this->databaseName) {
			throw new Exception('Database not found.');
		}
	}

	function setConfig(array $config)
	{
		$defaults['exclude'] = [];
		$defaults['dump-rows'] = [];

		$this->config = $config + $defaults;
	}

	protected abstract function getDatabaseName();
	protected abstract function getRows($table);
	protected abstract function getColumns($table);
	protected abstract function getIndexes($table);
	protected abstract function getExtras($table);
	protected abstract function getTables();

	function getDump()
	{
		$data = [];
		foreach ($this->getTables() as $table) {
			if (in_array($table, $this->config['exclude'])) {
				continue;
			}

			$data[$table]['columns'] = $this->getColumns($table);
			$data[$table]['indexes'] = $this->getIndexes($table);
			$data[$table]['extras'] = $this->getExtras($table);

			if (in_array($table, $this->config['dump-rows'])) {
				$data[$table]['rows'] = $this->getRows($table);
			}
		}

		$dump = new MigDump();
		$dump->data($data);
		return $dump;
	}
}

 ?>