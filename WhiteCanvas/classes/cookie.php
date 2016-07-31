<?php

Class Cookie
{
	protected $_content;
	protected $_name;
	protected $_expire;
	/* enable cookie on all the domain for now */
	protected $_path = '/';
	/* Will be used for encrypting cookies */
	protected $_modified = false;
	public function __construct($name, $path = '', $expire = null)
	{
		$this->_content = array();
		$this->_expire = isset($expire) ? (int)($expire) : (time() + 1728000);
		$this->_name = md5(_SECURE_KEY_.$name);
		$this->_path = ($path ? $path : $this->_path);
		$this->read();
	}
	/* Read $_COOKIE */
	public function read()
	{
		if ($this->exists()) {
			/* Decrypt cookie content */
			$content = Encryption::decrypt($_COOKIE[$this->_name]);
			$checksum = crc32(_COOKIE_IV_.substr($content, 0, strrpos($content, '~') + 1));
			/* unserialize */
			$main_container = explode('~', $content);
			foreach ($main_container as $key_and_value) {
				$array_key_value = explode('|', $key_and_value);
				if (count($array_key_value) == 2)
					$this->_content[$array_key_value[0]] = $array_key_value[1];
			}
			if (!isset($this->_content['date_add'])) {
				$this->_content['date_add'] = date('Y-m-d H:i:s');
			}
			if (!isset($this->_content['checksum']) || $this->_content['checksum'] != $checksum) {
				$this->delete();
			}
		} else {
			$this->_content['date_add'] = date('Y-m-d H:i:s');
		}
	}
	public function delete()
	{
		$this->_content = array();
		$this->_setCookie();
		unset($_COOKIE[$this->_name]);
		$this->_modified = true;
	}
	public function setExpire($expire)
	{
		$this->_expire = (int)($expire);
	}
	public function __get($key)
	{
		return isset($this->_content[$key]) ? $this->_content[$key] : false;
	}
	public function __isset($key)
	{
		return isset($this->_content[$key]);
	}
	public function __set($key, $value)
	{
		if (is_array($value) || preg_match('/~|\|/', $key.$value))
			die('Error in Cookie class invalid parameter $value in __set()\n');
		if (!$this->_modified && (!isset($this->_content[$key]) || (isset($this->_content[$key]) && $this->_content[$key] != $value)))
			$this->_modified = true;
		$this->_content[$key] = $value;
	}
	public function __unset($key)
	{
		if (isset($this->_content[$key]))
			$this->_modified = true;
		unset($this->_content[$key]);
	}
	public function getName()
	{
		return $this->_name;
	}
	public function exists()
	{
		return isset($_COOKIE[$this->_name]);
	}
	protected function _setCookie($cookie = null)
	{
		if ($cookie)
		{
			$content = Encryption::encrypt($cookie);
			$time = $this->_expire;
		}
		else
		{
			$content = 0;
			$time = 1;
		}
		if (PHP_VERSION_ID <= 50200) /* PHP version > 5.2.0 */
			return setcookie($this->_name, $content, $time, $this->_path, '', 0);
		else
			return setcookie($this->_name, $content, $time, $this->_path, '', 0, true);
	}
	public function __destruct()
	{
		$this->write();
	}
	public function write()
	{
		if (!$this->_modified || headers_sent())
			return;
		$cookie = '';
		/* Serialize cookie content */
		if (isset($this->_content['checksum'])) {
			unset($this->_content['checksum']);
		}
		foreach ($this->_content as $key => $value) {
			$cookie .= $key.'|'.$value.'~';
		}
		if ($cookie)
			$cookie .= 'checksum|'.crc32(_COOKIE_IV_.$cookie);
		$this->_modified = false;
		return $this->_setCookie($cookie);
	}
	public function getFamily($origin)
	{
		$result = array();
		if (count($this->_content) == 0) {
			return $result;
		}
		foreach ($this->_content as $key => $value) {
			if (strncmp($key, $origin, strlen($origin)) == 0) {
				$result[$key] = $value;
			}
		}
		return $result;
	}
	public function unsetFamily($origin)
	{
		$family = $this->getFamily($origin);
		foreach (array_keys($family) as $member) {
			unset($this->$member);
		}
	}
}