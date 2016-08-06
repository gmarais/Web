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

class Encryption
{
	private $_cypher;
	private $_mode;

	public function __construct($cypher = 'rijndael-256', $mode = 'ofb')
	{
		$this->_cypher = $cypher;
		$this->_mode = $mode;
	}

	static public function simpleEncrypt($plaintext)
	{
		$encryption = new Encryption();
		return $encryption->encrypt($plaintext);
	}

	static public function simpleDecrypt($crypttext)
	{
		$encryption = new Encryption();
		return $encryption->decrypt($crypttext);
	}

	public function encrypt($plaintext)
	{
		$td = mcrypt_module_open($this->_cypher, '', $this->_mode, '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, _COOKIE_KEY_, $iv);
		$crypttext = mcrypt_generic($td, $plaintext);
		mcrypt_generic_deinit($td);
		return $iv.$crypttext;
	}

	public function decrypt($crypttext)
	{
		$plaintext = "";
		$td        = mcrypt_module_open($this->_cypher, '', $this->_mode, '');
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