<?php 

require 'MigLibrary.php';


$dump = new MigDump;

$pdo = new PDO('mysql:dbname=test;host=localhost', 'root');
$dumper = new MigDumper($pdo, []);
$dump = $dumper->getDump();


//print nl2br(htmlentities($dump->getXml()));

//$dump->save('test.xml');

 //var_dump($dump->data());
$dump2 = new MigDump('test.xml');
 //var_dump($dump2->data());


$diff = new MigDiff;
var_dump($diff->compare($dump, $dump2));

$make = new MigSqlBuilder;


$make->createTable('nova', function($t) {
	$t->addColumn('jedna', []);
	$t->addColumn('dva', []);
});

$make->alterTable('nova', function($t) {
	$t->alterColumn('jedna', []);
});

$make->updateTable('nova', function($t) {
	$t->addRow([]);
});

var_dump($make->getSql());


class Migration123
{

	function up(MigSqlBuilder $b)
	{
		$b->createTable('nova', function($t) {
			$t->addColumn('jedna', []);
			$t->addColumn('dva', []);
		});
	}

	function down(MigSqlBuilder $b)
	{
	}
	
}

 ?>