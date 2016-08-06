<?php

class Like extends DbObject
{
	public $id_user;
	public $id_image;

	protected $_table_name = 'like';
	public $_definition = array(
		'id_user' => array(self::_TYPE_INT_),
		'id_image' => array(self::_TYPE_INT_)
	);

	static public function getLikesCount($id_image)
	{
		$query = new DbQuery();
		$query->from('like', 'l');
		$query->where('l.id_image', '=', (int)$id_image);
		$query->select('COUNT(l.id_like)');
		return Db::getInstance()->getValue($query->getSyntax(), $query->getData());
	}

	static public function getLike($id_user, $id_image)
	{
		$query = new DbQuery();
		$query->from('like', 'l');
		$query->where('l.id_user', '=', (int)$id_user);
		$query->where('l.id_image', '=', (int)$id_image);
		$query->select('id_like');
		$id_like = Db::getInstance()->getValue($query->getSyntax(), $query->getData());
		if ($id_like)
			return new Like($id_like);
		return null;
	}

	static public function userLikeUnlikeImage($id_user, $id_image)
	{
		$query = new DbQuery();
		$query->from('like', 'l');
		$query->where('l.id_user', '=', (int)$id_user);
		$query->where('l.id_image', '=', (int)$id_image);
		$query->select('id_like');
		$id_like = Db::getInstance()->getValue($query->getSyntax(), $query->getData());
		if ($id_like)
		{
			$like = new Like($id_like);
			$like->delete();
		}
		else
		{
			$like = new Like();
			$like->id_user = $id_user;
			$like->id_image = $id_image;
			$like->save();
		}
	}
}
