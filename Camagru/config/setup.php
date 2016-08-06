<?php
/*
 * Reformating db credentials the way I like them
 */
if (file_exists('config/database.php'))
{
	require_once 'config/database.php';
	$db_type = substr($DB_DSN, 0, strpos($DB_DSN, ':'));
	$raw_tab = explode(';', substr($DB_DSN, strpos($DB_DSN, ':') + 1, strlen($DB_DSN)));
	$db_tab = array();
	foreach ($raw_tab as $value)
	{
		$tmp = explode('=', $value);
		if (count($tmp) > 1)
			$db_tab[$tmp[0]] = $tmp[1];
	}
	$db_name = array_key_exists('dbname', $db_tab) ? $db_tab['dbname'] : '';
	$db_host = array_key_exists('host', $db_tab) ? $db_tab['host'] : '';
	$db_port = array_key_exists('port', $db_tab) ? $db_tab['port'] : '';
	Db::registerCredentials($db_type, $db_name, $db_host, $db_port, $DB_USER, $DB_PASSWORD);
}

/*
 * Creation of the database and update if necessary
 */
DbRevision::processRevision();
