<?php

class Image extends DbObject
{
	public $id_user;
	public $src;
	public $date;

	protected $_table_name = 'image';
	public $_definition = array(
		'id_user' => array(self::_TYPE_INT_),
		'src' => array(self::_TYPE_STRING_, 3, 255),
		'date' => array(self::_TYPE_DATE_)
	);

	public function delete()
	{
		$query = new DbQuery();
		$query->deleteFrom('like');
		$query->where('id_image', '=', (int)$this->id);
		Db::getInstance()->execute($query->getSyntax(), $query->getData());
		$query = new DbQuery();
		$query->deleteFrom('comment');
		$query->where('id_image', '=', (int)$this->id);
		Db::getInstance()->execute($query->getSyntax(), $query->getData());
		return parent::delete();
	}

	static public function loadImages($page_size, $offset = 0, $id_user = 0)
	{
		$query = new DbQuery();
		$query->select('id_image');
		$query->from('image', 'i');
		if ($id_user)
			$query->where('i.id_user', '=', (int)$id_user);
		$query->orderBy('i.date DESC');
		$query->limit($page_size, $offset);
		$images = Db::getInstance()->getRows($query->getSyntax(), $query->getData());
		if ($images)
		{
			foreach ($images as $key => $value)
			{
				$images[$key] = new Image($value['id_image']);
			}
		}
		return $images;
	}
}
