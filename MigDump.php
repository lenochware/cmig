<?php 

/**
 * It represents dump of the database.
 * Provides methods for load/save dump as xml file.
 * print_r $dump->data();
 */
class MigDump
{
	protected $data = [];

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

	function parseXml($xmlString)
	{
		$data = [];

		$xml = new SimpleXMLElement($xmlString);
		foreach ($xml->table as $table) {
			$tableName = (string)$table['name'];
			foreach ($table->column as $column) {
				$carr = current($column->attributes());
				$data[$tableName]['columns'][$carr['name']] = $carr;
			}

			$tarr = current($table->attributes());
			unset($tarr['name']);
			$data[$tableName]['extras'] = $tarr;
		}

		foreach ($xml->{'table-rows'} as $table) {
			$tableName = (string)$table['name'];
			$i = 0;
			foreach ($table->row as $row) {
				$carr = current($row->attributes());
				$data[$tableName]['rows'][$carr['ID']] = $carr;
			}
		}

		return $data;
	}

	function getXml()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n<database-dump>\r\n";

		foreach ($this->data as $tableName => $table) {
			$xmlTable = [];
			foreach ($table['columns'] as $key => $column) {
				$xmlTable[] = $this->getXmlColumn($column);
			}

			$xml .= $this->getXmlTableOpenTag($tableName, $table['extras'])."\r\n".implode("\r\n", $xmlTable)."\r\n".'</table>'."\r\n";
		}

		//rows
		foreach ($this->data as $tableName => $table) {
			if (!isset($table['rows'])) {
				continue;
			}

			$xmlTable = [];
			foreach ($table['rows'] as $key => $row) {
				$xmlTable[] = $this->getXmlRow($row);
			}
			$xml .= "<table-rows name=\"$tableName\">"."\r\n".implode("\r\n", $xmlTable)."\r\n".'</table-rows>'."\r\n";
		}


		return $xml."</database-dump>";
	}

	protected function getXmlTableOpenTag($tableName, array $options)
	{
		$s = [];
		foreach ($options as $key => $value) {
			$s[] = "$key=\"$value\"";
		}

		return "<table name=\"$tableName\" ".implode(' ', $s).">";
	}

	protected function getXmlColumn(array $column)
	{
		$s = [];
		foreach ($column as $key => $value) {

			$s[] = "$key=\"".htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8')."\"";
		}
		return "<column ".implode(" ", $s)." />";
	}

	protected function getXmlRow(array $row)
	{
		$s = [];
		foreach ($row as $key => $value) {
			$s[] = "$key=\"$value\"";
		}
		return "<row ".implode(" ", $s)." />";
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