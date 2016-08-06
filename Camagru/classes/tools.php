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

class Tools
{
	static public function isSubmit($label) {
		if (isset($_GET[$label]) || isset($_POST[$label])) {
			return true;
		}
		return false;
	}

	static public function getValue($label)
	{
		if (!empty($_POST[$label])) {
			return $_POST[$label];
		} else if (!empty($_GET[$label])) {
			return $_GET[$label];
		}
		return false;
	}

	static public function escape($string, $double = false)
	{
		if ($double)
			return preg_replace('/"/', '\"', $string);
		return preg_replace("/'/", "\'", $string);
	}

	static public function escapeSlashes($string)
	{
		return preg_replace('#/#', '\/', $string);
	}

	static public function redirect($url, $domain_name = true)
	{
		if ($domain_name)
		{
			$url = ltrim($url, '/');
			header('Location: '._DOMAIN_.$url);
		}
		else
			header('Location: '.$url);
	}

	static function getSignedInt($unsigned)
	{
		$int_max = pow(2, 31) - 1;
		if ($unsigned > $int_max) {
			return $unsigned - $int_max * 2 - 2;
		}
		return $unsigned;
	}
}
