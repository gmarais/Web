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

abstract class DbObject
{
	public $id;
	private $_loaded = false;
	/* @loaded
	 * Loaded is true if object is loaded from Db.
	 */
	protected $instance_Db;
	/* @instanceDb
	 * Minimise the number of instanciation of the Db class within
	 * the object
	 */
	protected $_table_name;
	protected $_prefixed = true;
	public $_definition;
	/* @definition
	 * The definition variable is an array that will contain all columns of the
	 * table with their associated types defined as follow :
	 * array('column'=> array(_TYPE_, MIN_SIZE, MAX_SIZE));
	 * Each column must have a public variable likely named in the class.
	 */
	const _TYPE_INT_ = 0;
	const _TYPE_FLOAT_ = 1;
	const _TYPE_STRING_ = 2;
	const _TYPE_BOOLEAN_ = 3;
	const _TYPE_DATE_ = 4;
	const _TYPE_PASSWORD_ = 5;
	const _TYPE_EMAIL_ = 6;

	public function isValidValue($value, $type)
	{
		$value = $this->castValueType($value, $type[0]);
		switch ($type[0])
		{
			case self::_TYPE_INT_:
			case self::_TYPE_FLOAT_:
				if (!empty($type[1]) && $type[1] != null && $value < $type[1])
					return false;
				if (!empty($type[2]) && $type[2] != null && $value > $type[2])
					return false;
				return true;
			case self::_TYPE_EMAIL_:
				if (!empty($type[1]) && $type[1] != null && strlen($value) < $type[1])
					return false;
				if (!empty($type[2]) && $type[2] != null && strlen($value) > $type[2])
					return false;
				if (!preg_match('#^[_a-z0-9-\.]+@[a-z0-9-\.]+\.[a-z0-9-\.]+$#', $value))
					return false;
				return true;
			case self::_TYPE_PASSWORD_:
				if (!preg_match('#.*[0-9].*#', $value))
					return false;
			case self::_TYPE_STRING_:
				if (!empty($type[1]) && $type[1] != null && strlen($value) < $type[1])
					return false;
				if (!empty($type[2]) && $type[2] != null && strlen($value) > $type[2])
					return false;
				return true;
			case self::_TYPE_DATE_:
				if (!preg_match('/^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1‌​1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]‌​[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0‌​2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02‌​)(-)(29)))(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))$/', $value))
					return false;
				return true;
			default:
				return true;
		}
	}

	protected function castValueType($value, $type)
	{
		switch ($type)
		{
			case self::_TYPE_INT_:
				return (int)$value;
			case self::_TYPE_FLOAT_:
				return (float)$value;
			case self::_TYPE_BOOLEAN_:
				return (bool)$value;
			case self::_TYPE_STRING_:
			case self::_TYPE_EMAIL_:
				return $value;
			default:
				return $value;
		}
	}

	public function isLoadedObject()
	{
		return $this->_loaded;
	}

	public function __construct($id = false)
	{
		if (!$this->_table_name)
			$this->_table_name = strtolower(get_class($this));
		$this->instance_Db = Db::getInstance();
		if ((int)$id)
		{
			$this->id = $id;
			$query = new DbQuery();
			foreach ($this->_definition as $column => $type)
			{
				$query->select($column);
			}
			$query->from($this->_table_name, null, $this->_prefixed);
			$query->where('id_'.$this->_table_name, '=', $id);
			$values = $this->instance_Db->getRow($query->getSyntax(), $query->getData());
			if (!$values)
				return;
			foreach ($this->_definition as $column => $type)
			{
				$this->$column = stripcslashes($this->castValueType($values[$column], $type[0]));
			}
			$this->_loaded = true;
		}
	}

	public function save()
	{
		if (!$this->id)
		{
			return $this->create();
		}
		return $this->update();
	}

	public function create()
	{
		$query = new DbQuery();
		$query->insertInto($this->_table_name, array_keys($this->_definition), $this->_prefixed);
		foreach ($this->_definition as $column => $type)
		{
			$query->values($this->castValueType($this->$column, $type[0]));
		}
		if ($this->instance_Db->execute($query->getSyntax(), $query->getData()))
		{
			$this->id = $this->instance_Db->getLastInsertedId();
			return true;
		}
		return false;
	}

	public function update()
	{
		$query = new DbQuery();
		$query->update($this->_table_name, null, $this->_prefixed);
		foreach ($this->_definition as $column => $type)
		{
			$query->set($column, $this->castValueType($this->$column, $type[0]));
		}
		$query->where('id_'.$this->_table_name, '=', (int)$this->id);
		return $this->instance_Db->execute($query->getSyntax(), $query->getData());
	}

	public function delete()
	{
		$query = new DbQuery();
		$query->deleteFrom($this->_table_name, $this->_prefixed);
		$query->where('id_'.$this->_table_name, '=', (int)$this->id);
		return $this->instance_Db->execute($query->getSyntax(), $query->getData());
	}
}
