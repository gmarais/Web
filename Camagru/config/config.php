<?php

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

/**
 * Revision of data base :
 */
require_once _CONFIG_DIR_.'/setup.php';

/**
 * Utilisation du cache quand c'est possible :
 */
// CacheLoader::load();
