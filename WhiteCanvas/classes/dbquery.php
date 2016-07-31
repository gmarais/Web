<?php

/* This class helps formating sql query strings faster and with more flexibility
 * */
class DbQuery
{
	static private $_last_id = 1;
	private $_data;
	private $_select;
	private $_insert_into;
	private $_values;
	private $_delete_from;
	private $_from;
	private $_join;
	private $_where;
	private $_group_by;
	private $_order_by;
	private $_limit;
	private $_on_duplicate_key_update;
	private $_update;
	private $_set;

	public function __construct()
	{
		$this->_join = '';
	}

	public function select($value, $alias=null)
	{
		if ($this->_select) {
			$this->_select .= ', '.$value.($alias ? ' AS '.$alias : '');
		} else {
			$this->_select = 'SELECT '.$value.($alias ? ' AS '.$alias : '');
		}
	}

	public function insertInto($table, $columns, $prefixed = true)
	{
		$this->_insert_into = 'INSERT INTO '.($prefixed ? _DB_PREFIX_ : '').$table.' (';
		if (is_array($columns)) {
			$columns = array_values($columns);
			foreach ($columns as $key => $col) {
				$this->_insert_into .= ($key ? ', `' : '`').($col ? $col : '0').'`';
			}
			$this->_insert_into .= ')';
		} else {
			$this->_insert_into .= $columns.')';
		}
	}

	public function update($table, $alias, $prefixed = true)
	{
		if (!$this->_update) {
			$this->_update = 'UPDATE '.($prefixed ? _DB_PREFIX_ : '').$table.($alias ? ' '.$alias : '');
		} else {
			$this->_update .= ', '.($prefixed ? _DB_PREFIX_ : '').$table.($alias ? ' '.$alias : '');
		}
	}

	public function set($column, $data)
	{
		$this->_data[':s'.self::$_last_id] = $data;
		if (!$this->_set) {
			$this->_set = "\nSET ".$column.'=:s'.self::$_last_id;
		} else {
			$this->_set .= ", ".$column.'=:s'.self::$_last_id;
		}
		self::$_last_id++;
	}

	public function values($value)
	{
		$this->_data[':v'.self::$_last_id] = $value;
		if (!$this->_values) {
			$this->_values = "\nVALUES(:v".self::$_last_id.')';
		} else {
			$this->_values = substr($this->_values, 0, -1);
			$this->_values .= ', :v'.self::$_last_id.')';
		}
		self::$_last_id++;
	}

	public function onDuplicateKeyUpdate($rule)
	{
		if ($rule) {
			$this->_on_duplicate_key_update = "\nON DUPLICATE KEY UPDATE ".$rule;
		} else {
			$this->_on_duplicate_key_update = null;
		}
	}

	public function deleteFrom($table, $prefixed = true)
	{
		if ($this->_delete_from) {
			$this->_delete_from .= ', '.($prefixed ? _DB_PREFIX_ : '').$table;
		} else {
			$this->_delete_from = 'DELETE FROM '.($prefixed ? _DB_PREFIX_ : '').$table;
		}
	}

	public function from($table, $alias = null, $prefixed = true)
	{
		$this->_from = "\nFROM `".(($prefixed)?_DB_PREFIX_:'').$table.'` '.$alias;
	}

	public function join($sql)
	{
		$this->_join .= "\n".$sql;
	}

	public function innerJoin($table, $alias = null, $on, $prefixed = true)
	{
		$this->_join .= "\nINNER JOIN `".(($prefixed)?_DB_PREFIX_:'').$table.'` '.$alias."\n    ON ".$on;
	}

	public function leftJoin($table, $alias = null, $on, $prefixed = true)
	{
		$this->_join .= "\nLEFT JOIN `".(($prefixed)?_DB_PREFIX_:'').$table.'` '.$alias."\n    ON ".$on;
	}

	public function outerJoin($table, $alias = null, $on, $prefixed = true)
	{
		$this->_join .= "\nOUTER JOIN `".(($prefixed)?_DB_PREFIX_:'').$table.'` '.$alias."\n    ON ".$on;
	}

	public function where($column, $operator, $data)
	{
		$this->_data[':w'.self::$_last_id] = $data;
		if (!$this->_where) {
			$this->_where = "\nWHERE (".$column.' '.$operator.' :w'.self::$_last_id.')';
		} else {
			$this->_where .= "\n    AND (".$column.' '.$operator.' :w'.self::$_last_id.')';
		}
		self::$_last_id++;
	}

	public function groupBy($value)
	{
		if (!$this->_group_by) {
			$this->_group_by = "\nGROUP BY ".$value;
		} else {
			$this->_group_by .= ', '.$value;
		}
	}

	public function orderBy($value)
	{
		if (!$this->_order_by) {
			$this->_order_by = "\nORDER BY ".$value;
		} else {
			$this->_order_by .= ', '.$value;
		}
	}

	public function limit($value)
	{
		$this->_limit = "\nLIMIT ".(int)$value;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function getSyntax()
	{
		return $this->__toString();
	}

	public function __toString()
	{
		if (!$this->_insert_into && !$this->_delete_from && !$this->_update)
		{
			return (
				$this->_select
				.$this->_from
				.$this->_join
				.$this->_where
				.$this->_group_by
				.$this->_order_by
				.$this->_limit
			);
		}
		elseif ($this->_insert_into)
		{
			return (
				$this->_insert_into
				.($this->_values ? $this->_values : $this->_select
					.$this->_from
					.$this->_join
					.$this->_where
					.$this->_group_by
					.$this->_order_by
					.$this->_limit
				)
				.$this->_on_duplicate_key_update
			);
		}
		elseif ($this->_delete_from)
		{
			return (
				$this->_delete_from
				.$this->_join
				.$this->_where
				.$this->_order_by
				.$this->_limit
			);
		}
		elseif ($this->_update && $this->_set)
		{
			return (
				$this->_update
				.$this->_join
				.$this->_set
				.$this->_where
				.$this->_order_by
				.$this->_limit
			);
		}
	}
}
