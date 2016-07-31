<?php

class Encryption
{
	static $cypher = 'blowfish';
	static $mode   = 'cfb';
	static public function encrypt($plaintext)
	{
		$td = mcrypt_module_open(self::$cypher, '', self::$mode, '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, _COOKIE_KEY_, $iv);
		$crypttext = mcrypt_generic($td, $plaintext);
		mcrypt_generic_deinit($td);
		return $iv.$crypttext;
	}
	static public function decrypt($crypttext)
	{
		$plaintext = "";
		$td        = mcrypt_module_open(self::$cypher, '', self::$mode, '');
		$ivsize    = mcrypt_enc_get_iv_size($td);
		$iv        = substr($crypttext, 0, $ivsize);
		$crypttext = substr($crypttext, $ivsize);
		if ($iv)
		{
			mcrypt_generic_init($td, _COOKIE_KEY_, $iv);
			$plaintext = mdecrypt_generic($td, $crypttext);
		}
		return $plaintext;
	}
}