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

/* NEEDED DEFINES:
<CORE>
	_DOMAIN_
	_ROOT_DIR_
	_CONFIG_DIR_
	_CLASSES_DIR_
	_CONTROLLERS_DIR_
	_DB_PREFIX_
	_DB_OBJECTS_DIR_
</CORE>
<CLASSES>
	_SECURE_KEY_
	_COOKIE_KEY_
	_COOKIE_IV_
	_CACHE_DIR_
	_TEMPLATES_DIR_
	_TRANSLATIONS_DIR_
	_MAIL_SENDER_
</CLASSES>
*/

// Domain
$prefix = '/camagru/';
if (strstr($_SERVER['SERVER_NAME'], 'localhost')
	|| strstr($_SERVER['SERVER_NAME'], $_SERVER['SERVER_ADDR'])
)
{
	if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])
		define('_DOMAIN_', 'https://'.$_SERVER['HTTP_HOST'].$prefix);
	else
		define('_DOMAIN_', 'http://'.$_SERVER['HTTP_HOST'].$prefix);
}
else
{
	define('_DOMAIN_', '//'.$_SERVER['SERVER_NAME']);
}

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
define('_MAIL_SENDER_', '"camagru"<trogallart@hotmail.com>');

// DB Variables
define('_DB_PREFIX_', 'ft_');

// Variables
define('_SECURE_KEY_', '_LL`iA6(qd2]bB@YasWBI_eHLQyU6-q*CB2+QZ}hug#w4$0');
define('_COOKIE_KEY_', '685687846121432138498794');
define('_COOKIE_IV_', 'L1ZH3DI6V');

// Activation of the cache for the release
if (stristr($_SERVER['SERVER_NAME'], 'dev.') == false)
{
	define('_CACHE_', true);
}
else
{
	define('_DEV_', true);
}
