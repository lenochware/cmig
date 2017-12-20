<?php

// Migration_{MIGRATION_ID}
class Migration
{
	function up(MigSqlBuilder $builder)
	{
{MIGRATION_UP_CODE}
	}

	function down(MigSqlBuilder $builder)
	{
{MIGRATION_DOWN_CODE}
	}
}