<?php 

require 'MigLibrary.php';


$dump = new MigDump;

$pdo = new PDO('mysql:dbname=test;host=localhost', 'root');
$dumper = new MigDumper($pdo, []);
$dumper->getDump();

$diff = new MigDiff;

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