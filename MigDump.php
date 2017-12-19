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

 ?>