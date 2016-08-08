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

class Db
{
	/* singleton */
	private static $instance;
	private $_type = '';
	private $_host = '';
	private $_port = '';
	private $_user = '';
	private $_password = '';
	private $_dbname = '';
	private $_pdo = null;
	private $_errors;
	private $_last_inserted_id;

	private function readCredentials()
	{
		$credentials = substr(file_get_contents(_CONFIG_DIR_."/encoded/dbcredentials.php"), 6);
		$credentials = json_decode(Encryption::simpleDecrypt($credentials), true);
		if ($credentials)
		{
			$this->_type = $credentials["type"];
			$this->_host = $credentials["host"];
			$this->_port = $credentials["port"];
			$this->_user = $credentials["user"];
			$this->_password = $credentials["password"];
			$this->_dbname = $credentials["dbname"];
		}
	}

	static public function registerCredentials($type, $dbname, $host, $port, $user, $password)
	{
		$array["type"] = $type;
		$array["dbname"] = $dbname;
		$array["host"] = $host;
		$array["port"] = $port;
		$array["user"] = $user;
		$array["password"] = $password;
		if (!is_dir(_CONFIG_DIR_."/encoded"))
			@mkdir(_CONFIG_DIR_."/encoded", 0755, true);
		if (!file_exists(_CONFIG_DIR_."/encoded/.htaccess"))
			file_put_contents(_CONFIG_DIR_."/encoded/.htaccess", "Deny from all");
		file_put_contents(_CONFIG_DIR_."/encoded/dbcredentials.php", "<?php\n".Encryption::simpleEncrypt(json_encode($array, JSON_PRETTY_PRINT)));
	}

	private function connect()
	{
		if ($this->_pdo)
			$this->disconnect();
		try
		{
			$dsn = $this->_type.':dbname='.$this->_dbname.';host='.$this->_host;
			if ($this->_port)
				$dsn .= ';port='.$this->_port;
			$this->_pdo = new PDO($dsn, $this->_user, $this->_password);
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
		if (file_exists(_CONFIG_DIR_."/encoded/dbcredentials.php"))
		{
			$this->readCredentials();
			$this->connect();
		}
	}

	public function __destruct()
	{
		$this->disconnect();
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
		if ($this->checkConnection())
		{
			try
			{
				$request = $this->_pdo->prepare($syntax);
				$this->_pdo->beginTransaction();
				$result = $request->execute($data);
				$this->_last_inserted_id = $this->_pdo->lastInsertId();
				$this->_pdo->commit();
				return $request;
			}
			catch (PDOException $e)
			{
				$this->_pdo->rollback();
				$this->_errors[] = $e->getMessage();
			}
		}
		return false;
	}

	public function getRows($syntax, $data)
	{
		$this->checkConnection();
		try
		{
			$result = $this->execute($syntax, $data);
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
				if ($row && is_array($row))
					return reset($row);
				else
					return false;
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
		return  $this->_last_inserted_id;
	}

	public function popLastError()
	{
		if ($this->_errors != null)
			return array_pop($this->_errors);
		else
			return false;
	}
}
