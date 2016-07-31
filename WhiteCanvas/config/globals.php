<?php

// Domain
define('_DOMAIN_', '//'.$_SERVER['SERVER_NAME']);

// Directories
define('_ROOT_DIR_', '');
define('_CACHE_DIR_', _ROOT_DIR_.'cache');
define('_CONFIG_DIR_', _ROOT_DIR_.'config');
define('_CLASSES_DIR_', _ROOT_DIR_.'classes');
define('_CONTROLLERS_DIR_', _ROOT_DIR_.'controllers');
define('_DB_OBJECTS_DIR_', _ROOT_DIR_.'db_objects');
define('_TRANSLATIONS_DIR_', _ROOT_DIR_.'translations');
define('_TEMPLATES_DIR_', _ROOT_DIR_.'templates');

// Mail
define('_MAIL_SENDER_', '"'._DOMAIN_.'"<noreply@'._DOMAIN_.'>');

// DB Variables
define('_DB_PREFIX_', 'wc_');

// Activation of the cache for the release
if (stristr($_SERVER['SERVER_NAME'], 'dev.') == false)
{
	define('_CACHE_', true);
}
else
{
	define('_DEV_', true);
}