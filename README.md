# What is CMig

PHP database migration tool. It can create php migration file right from your database.

Now it is in alpha stage. Do not use!

### Features

* Generate migration file from database
* Make XML dump of your database
* Perform diff of two dumps
* Can migrate table data as well

### Requirements
- PHP 5.4

### Examples

**Get database dump and save as xml**
```php
$pdo = new PDO('mysql:dbname=test;host=localhost', 'root');
$dumper = new MigMysqlDumper($pdo, []);
$dump = $dumper->getDump();
$dump->save('test.xml');
```

**Create php migration file by comparing of two dumps**
```php
$diff = new MigDiff($dump, $dump2);
file_put_contents('Migration1234.php', $diff->createPhpMigration());
```


**Convert php migration file 'Migration1234.php' to Mysql commands**
```php
require 'Migration1234.php';

$builder = new MigMysqlBuilder;
$migration = new Migration;
$migration->up($builder);
var_dump($builder->getSql());
```

### MIT License

Copyright (c) 2017 CMig library

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.