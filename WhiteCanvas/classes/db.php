<?php

class Db
{
	/* singleton */
	private static $instance;
	private $_type = '';
	private $_host = '';
	private $_login = '';
	private $_password = '';
	private $_dbname = '';
	private $_pdo = null;
	private $_errors;

	private function readCredentials()
	{
		$credentials = file_get_contents(_CONFIG_DIR_."/dbcredentials.json");
		$credentials = json_decode($credentials, true);
		if ($credentials)
		{
			$this->_type = $credentials["type"];
			$this->_host = $credentials["host"];
			$this->_login = $credentials["login"];
			$this->_password = $credentials["password"];
			$this->_dbname = $credentials["dbname"];
		}
	}

	private function connect()
	{
		if ($this->_pdo)
			$this->disconnect();
		try
		{
			$this->_pdo = new PDO($this->_type.':host='.$this->_host.';dbname='.$this->_dbname, $this->_login, $this->_password);
			$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			$this->_errors[] = $e->getMessage();
		}
	}

	private function disconnect()
	{
		if ($this->_pdo)
			$this->_pdo = null;
	}

	public function __construct()
	{
		$this->readCredentials();
		$this->connect();
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	static public function registerCredentials($type, $host, $dbname, $login, $password)
	{
		$array["type"] = $type;
		$array["host"] = $host;
		$array["dbname"] = $dbname;
		$array["login"] = $login;
		$array["password"] = $password;
		file_put_contents(_CONFIG_DIR_."/dbcredentials.json", json_encode($array));
	}

	static public function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Db();
		}
		return self::$instance;
	}

	public function checkConnection()
	{
		if (!$this->_pdo)
		{
			$this->connect();
		}
		else
		{
			try
			{
				$this->_pdo->query('SELECT 1');
			}
			catch (PDOException $e)
			{
				$this->connect();
			}
		}
		return false;
	}

	public function execute($syntax, $data = null)
	{
		$this->checkConnection();
		try
		{
			$request = $this->_pdo->prepare($syntax);
			$this->_pdo->beginTransaction();
			$result = $request->execute($data);
			$this->_pdo->commit();
			return $request;
		}
		catch (PDOException $e)
		{
			$this->_pdo->rollback();
			$this->_errors[] = $e->getMessage();
		}
		return false;
	}

	public function getRows($syntax, $data)
	{
		$this->checkConnection();
		try
		{
			$result = $this->execute($syntax, $data);
			echo $this->popLastError();
			if ($result && $result->columnCount() > 0) {
				$rows = $result->fetchAll(PDO::FETCH_ASSOC);
				$result = null;
				return $rows;
			}
			if ($result)
				$result = null;
		}
		catch (PDOException $e)
		{
			$this->_errors[] = $e->getMessage();
		}
		return false;
	}

	public function getValue($syntax, $data)
	{
		$this->checkConnection();
		try
		{
			$result = $this->execute($syntax."\nLIMIT 1;", $data);
			if ($result && $result->columnCount() > 0) {
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$result = null;
				return reset($row);
			}
			if ($result)
				$result = null;
		}
		catch (PDOException $e)
		{
			$this->_errors[] = $e->getMessage();
		}
		return false;
	}

	public function getRow($syntax, $data)
	{
		$this->checkConnection();
		try
		{
			$result = $this->execute($syntax."\nLIMIT 1;", $data);
			if ($result &&  $result->columnCount() > 0) {
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$result = null;
				return $row;
			}
			if ($result)
				$result = null;
		}
		catch (PDOException $e)
		{
			$this->_errors[] = $e->getMessage();
		}
		return false;
	}

	public function getLastInsertedId()
	{
		$this->checkConnection();
		try
		{
			return $this->_pdo->lastInsertId();
		}
		catch (PDOException $e)
		{
			$this->_errors[] = $e->getMessage();
		}
		return false;
	}

	public function popLastError()
	{
		if ($this->_errors != null)
			return array_pop($this->_errors);
		else
			return false;
	}
}
