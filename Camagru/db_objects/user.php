<?php

class User extends DbObject
{
	public $nickname;
	public $email;
	public $password;
	public $key_hash;
	public $registration_date;
	public $active;

	protected $_table_name = 'user';
	public $_definition = array(
		'nickname' => array(self::_TYPE_STRING_, 3, 24),
		'email' => array(self::_TYPE_EMAIL_, 3, 255),
		'password' => array(self::_TYPE_PASSWORD_, 0, 32),
		'key_hash' => array(self::_TYPE_STRING_, 0, 32),
		'registration_date' => array(self::_TYPE_DATE_),
		'active' => array(self::_TYPE_BOOLEAN_),
	);

	public function checkPassword($password, $encrypted = false)
	{
		if (!$password)
			return false;
		if ($encrypted)
		{
			return $this->password == $password;
		}
		return $this->password == MD5(_SECURE_KEY_.$password.$this->key_hash);
	}

	static public function loadByNickname($nickname)
	{
		$query = new DbQuery();
		$query->select('id_user');
		$query->from('user', 'u');
		$query->where('u.nickname ', '=', $nickname);
		return new User((int)Db::getInstance()->getValue($query->getSyntax(), $query->getData()));
	}

	static public function loadByEmail($email)
	{
		$query = new DbQuery();
		$query->select('id_user');
		$query->from('user', 'u');
		$query->where('u.email ', '=', $email);
		return new User((int)Db::getInstance()->getValue($query->getSyntax(), $query->getData()));
	}

	static public function loadMembers()
	{
		$query = new DbQuery();
		$query->select('id_user');
		$query->from('user', 'u');
		$query->orderBy('u.id_user DESC');
		$members = Db::getInstance()->getRows($query->getSyntax(), $query->getData());
		if ($members)
		{
			foreach ($members as $key => $value)
			{
				$members[$key] = new User($value['id_user']);
			}
		}
		return $members;
	}
}
