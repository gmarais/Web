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

session_start();
class Session
{
	static public function has($key)
	{
		return isset($_SESSION[$key]);
	}
	static public function put($key, $value)
	{
		return $_SESSION[$key] = $value;
	}
	static public function delete($key)
	{
		if (self::has($key))
			unset($_SESSION[$key]);
	}
	static public function get($key)
	{
		if (self::has($key))
			return $_SESSION[$key];
	}
}