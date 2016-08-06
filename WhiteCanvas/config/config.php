<?php
/* ******************************************************** _ *** _ ******** */
/*                                                   ______//_____\\______   */
/*   WhiteCanvas 2016                               |                     |  */
/*                                                  |                     |  */
/*   Created by Gabriel Marais                      |                     |  */
/*                                                  |                     |  */
/*                                                  |_____.____.______W_C_|  */
/*   https://github.com/gmarais                     |_____________________|  */
/*                                                  //         ||        \\  */
/* *********************************************** // ******************* \\ */

/*
 * Error reporting for developpement mode :
 */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


/*
 * Setting globals
 */
require_once 'config/globals.php';


/*
 * Require AutoLoader function.
 */
require_once _CLASSES_DIR_.'/autoloader.php';

/*
 * Do this once to create the encrypted database credentials file:
 */
//$db_type = 'mysql';
//$db_name = 'dbname';
//$db_host = 'hostname';
//$db_port = 'port';
//$db_user = 'root';
//$db_pass = 'password';
//Db::registerCredentials($db_type, $db_name, $db_host, $db_port, $db_user, $db_pass);

/*
 * Revision of data base :
 */
DbRevision::processRevision();

/**
 * Utilisation du cache quand c'est possible :
 */
// CacheLoader::load();
