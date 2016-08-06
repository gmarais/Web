<?php

class Comment extends DbObject
{
	public $id_user;
	public $id_image;
	public $comment_text;
	public $date;

	protected $_table_name = 'comment';
	public $_definition = array(
		'id_user' => array(self::_TYPE_INT_),
		'id_image' => array(self::_TYPE_INT_),
		'comment_text' => array(self::_TYPE_STRING_, 0, 2000),
		'date' => array(self::_TYPE_DATE_)
	);

	static public function getCommentsCount($id_image)
	{
		$query = new DbQuery();
		$query->from('comment', 'c');
		$query->where('c.id_image', '=', (int)$id_image);
		$query->select('COUNT(c.id_comment)');
		return Db::getInstance()->getValue($query->getSyntax(), $query->getData());
	}

	static public function getComments($id_image, $page_size, $offset)
	{
		$query = new DbQuery();
		$query->from('comment', 'c');
		$query->where('c.id_image', '=', (int)$id_image);
		$query->select('c.id_comment');
		$query->limit($page_size, $offset);
		$query->orderBy('c.date ASC');
		$comments = Db::getInstance()->getRows($query->getSyntax(), $query->getData());
		if ($comments)
		{
			foreach ($comments as $key => $value)
			{
				$comments[$key] = new Comment($value['id_comment']);
			}
		}
		return $comments;
	}
}
