<?php

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