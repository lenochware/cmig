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
	function data(array $data = null)
	{
		if ($data) {
			$this->data = $data;
		}

		return $this->data;
	}

	//bug? it returns false/null as empty string
	function parseXml($xmlString)
	{
		$data = [];

		$xml = new SimpleXMLElement($xmlString);
		foreach ($xml->table as $table) {
			$tableName = (string)$table['name'];
			foreach ($table->column as $column) {
				$carr = current($column->attributes());
				$data[$tableName][$carr['name']] = $carr;
			}
		}

		return $data;
	}

	function getXml()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n<database-dump>\r\n";
		foreach ($this->data as $tableName => $columns) {
			$xmlTable = [];
			foreach ($columns as $key => $column) {
				$xmlTable[] = $this->getXmlColumn($column);
			}
			$xml .= "<table name=\"$tableName\">"."\r\n".implode("\r\n", $xmlTable)."\r\n".'</table>'."\r\n";
		}
		return $xml."</database-dump>";
	}

	protected function getXmlColumn(array $column)
	{
		$s = [];
		foreach ($column as $key => $value) {
			$s[] = "$key=\"$value\"";
		}
		return "<column ".implode(" ", $s)." />";
	}

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



/**
 * Create diff of two database dumps as php migration script.
 * Database dump is represented as class MigDump.
 * $phpMigration = $migDiff->createPhpMigration($dump1, $dump2);
 */
class MigDiff
{
	function createPhpMigration(MigDump $a, MigDump $b)
	{
		$diff = $this->compare($a, $b);
		$s = $this->buildPhp($diff);
		return $s;
	}

	protected function compareTables($tableName, $a, $b)
	{
		$diff = [];

		$ka = array_keys($a);
		$kb = array_keys($b);

		$columns = array_unique(array_merge($ka, $kb));

		foreach ($columns as $col) {
			if (in_array($col, $ka) and !in_array($col, $kb)) {
				$diff[] = ['command' => 'dropColumn', 'table' => $tableName, 'name' => $col];
			}
			elseif(in_array($col, $kb) and !in_array($col, $ka)) {
				$diff[] = ['command'=> 'addColumn', 'table' => $tableName, 'name' => $col, 'attrib' => $b[$col]];
			}
			else {
				$diffAttrib = array_diff_assoc($a[$col], $b[$col]);
				if ($diffAttrib) {
					$diff[] = ['command'=> 'changeColumn', 'table' => $tableName, 'name' => $col, 'attrib' => $diffAttrib];					
				}
			}
		}

		return $diff;
	}

	function compare(MigDump $dumpA, MigDump $dumpB)
	{
		$diff = [];

		$a = $dumpA->data();
		$b = $dumpB->data();

		$ka = array_keys($a);
		$kb = array_keys($b);

		$tables = array_unique(array_merge($ka, $kb));

		foreach ($tables as $t) {
			if (in_array($t, $ka) and !in_array($t, $kb)) {
				$diff[] = ['command' => 'dropTable', 'name' => $t];
			}
			elseif(in_array($t, $kb) and !in_array($t, $ka)) {
				$diff[] = ['command'=> 'createTable', 'name' => $t, 'columns' => $b[$t]];
			}
			else {
				$diff = array_merge($diff, $this->compareTables($t, $a[$t], $b[$t]));
			}
		}

		return $diff;
	}

	function buildPhp(array $diff)
	{

	}
}

/**
 * Provides interface for creating/altering database schema and data.
 * Translate php commands to sql statements for supported database.
 * $builder->dropTable('test');  $builder->createTable(...) ...
 * $sql = $builder->getSql();
 */
abstract class MigSqlBuilder
{
	protected $sql;

  abstract function createTable($name, $def);
  abstract function dropTable($name);
	abstract function updateTable($name, $data);
	abstract function addColumn($table, $name, $def);
	abstract function dropColumn($table, $name);
	abstract function changeColumn($table, $name, $def);

	function getSql()
	{
		return $this->sql;
	}
}

 ?>