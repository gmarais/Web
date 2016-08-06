<?php

class GalleryController extends Controller
{
	public $user;

	protected function checkToken()
	{
		$cookie = new Cookie('camagru');
		if ($cookie->exists())
		{
			$this->user = new User((int)$cookie->id_user);
			if ($this->user->isLoadedObject())
			{
				return;
			}
		}
		else
			$this->user = new User();
	}

	protected function render()
	{
		$this->addCSS('css/gallery.css');
		$this->addJS('js/gallery.js');
		$tpl = new Tpl();
		$tpl->assign("domain_link", _DOMAIN_);
		$tpl->assign("js_files", $this->fetchJS());
		$tpl->assign("css_files", $this->fetchCSS());
		if ($this->user->isLoadedObject())
			$tpl->assign("logout_button", '<a href="'._DOMAIN_.'workshop"><button id="workshop">workshop</button></a><button id="logout">logout</button>');
		else
			$tpl->assign("logout_button", '<a href="'._DOMAIN_.'login"><button id="login">login</button></a>');
		echo $tpl->fetch("gallery/gallery.html");
	}

	protected function fetchImageFeed($image)
	{
		$tpl = new Tpl();
		$tpl->assign("domain_link", _DOMAIN_);
		$tpl->assign('date', $image->date);
		$user = new User($image->id_user);
		if ($user->isLoadedObject())
		{
			$tpl->assign('user_name', $user->nickname);
			if ($this->user->isLoadedObject())
			{
				$like_button = '';
				if (Like::getLike($this->user->id, $image->id))
					$like_button = '<button class="like unlike" data-id="'.$image->id.'">Unlike</button>';
				else
					$like_button = '<button class="like" data-id="'.$image->id.'">Like</button>';
				if ($this->user->id == $image->id_user)
				{
					$tpl->assign('like_and_delete_button', '<button class="delete_image" data-id="'.$image->id.'">Delete</button>'.$like_button);
				}
				else
				{
					$tpl->assign('like_and_delete_button', $like_button);
				}
			}
			else
			{
				$tpl->assign('like_and_delete_button', '');
			}
		}
		$tpl->assign('likes', (int)Like::getLikesCount($image->id));
		$tpl->assign('comments', (int)Comment::getCommentsCount($image->id));
		$tpl->assign('id_image', $image->id);
		$tpl->assign('image_src', $image->src);
		return $tpl->fetch('gallery/helpers/image_feed.html');
	}

	protected function processGetImageFeed()
	{
		$id_image = (int)Tools::getValue('id_image');
		$image = new Image($id_image);
		if (!$image->isLoadedObject())
		{
			echo new JsonResponse('KO', array('message' => 'Image not found...'));
			return ;
		}
		echo new JsonResponse('OK', array('image' => $this->fetchImageFeed($image, false)));
	}

	protected function processFeedImages()
	{
		$feed_size = (int)Tools::getValue('feed_size');
		$feed_offset = (int)Tools::getValue('feed_offset');
		if (!$feed_size)
			$feed_size = 3;
		$images_feed = '';
		$images = Image::loadImages($feed_size, $feed_offset);
		foreach ($images as $image)
		{
			$images_feed .= $this->fetchImageFeed($image);
		}
		echo new JsonResponse('OK', array('feed' => $images_feed));
	}

	protected function processLikeUnlikeImage()
	{
		if ($this->user->isLoadedObject())
		{
			$id_image = (int)Tools::getValue('id_image');
			Like::userLikeUnlikeImage($this->user->id, $id_image);
			$likes = (int)Like::getLikesCount($id_image);
			echo new JsonResponse('OK', array('likes' => $likes));
		}
		else
			echo new JsonResponse('KO', array('message' => 'You are not logged in...'));
	}

	protected function fetchCommentFeed($comment)
	{
		$tpl = new Tpl();
		$tpl->assign("domain_link", _DOMAIN_);
		$tpl->assign('id_comment', $comment->id);
		$tpl->assign('date', $comment->date);
		$tpl->assign('comment_text', $comment->comment_text);
		$user = new User($comment->id_user);
		if ($user->isLoadedObject())
		{
			$tpl->assign('user_name', $user->nickname);
			if ($this->user->isLoadedObject() && $this->user->id == $comment->id_user)
			{
				$tpl->assign('delete_button', '<button class="delete_comment" data-id="'.$comment->id.'">Delete</button>');
			}
			else
			{
				$tpl->assign('delete_button', '');
			}
		}
		return $tpl->fetch('gallery/helpers/comment_feed.html');
	}

	protected function processFeedComments()
	{
		$id_image = (int)Tools::getValue('id_image');
		$feed_size = (int)Tools::getValue('feed_size');
		$feed_offset = (int)Tools::getValue('feed_offset');
		if (!$feed_size)
			$feed_size = 3;
		$comments = Comment::getComments($id_image, $feed_size, $feed_offset);
		$comments_feed = '';
		foreach ($comments as $comment)
		{
			$comments_feed .= $this->fetchCommentFeed($comment);
		}
		echo new JsonResponse('OK', array('feed' => $comments_feed, 'id_image' => $id_image));
	}

	protected function processCommentImage()
	{
		$id_image = (int)Tools::getValue('id_image');
		$image = new Image($id_image);
		if (!$image->isLoadedObject())
		{
			echo new JsonResponse('KO', array('message' => "Image not found...$id_image"));
			return;
		}
		$comment_text = Tools::getValue('comment_text');
		if ($this->user->isLoadedObject() && $comment_text && strlen($comment_text) > 3)
		{
			$comment = new Comment();
			$comment->id_user = $this->user->id;
			$comment->id_image = $id_image;
			$comment->comment_text = $comment_text;
			$comment->date = date('Y-m-d H:i:s');
			if ($comment->save())
			{
				$comments = (int)Comment::getCommentsCount($id_image);
				echo new JsonResponse('OK', array('comments' => $comments));
			}
			else
			{
				echo new JsonResponse('KO', array('message' => 'An error occured while saving your comment...'.(Db::getInstance()->popLastError())));
				return;
			}
		}
		else
			echo new JsonResponse('KO', array('message' => 'You are not logged in...'));
	}

	protected function processDeleteComment()
	{
		$id_comment = (int)Tools::getValue('id_comment');
		$comment = new Comment($id_comment);
		if ($comment->isLoadedObject() && $comment->delete())
		{
			if ($comment->id_user == $this->user->id)
			{
				$id_image = $comment->id_image;
				if ($comment->delete())
				{
					$comments = (int)Comment::getCommentsCount($id_image);
					echo new JsonResponse('OK', array('comments' => $comments));
				}
			}
			else
				echo new JsonResponse('KO', array('message' => 'You cant remove comments from other users...'));
		}
		else
			echo new JsonResponse('KO', array('message' => 'Error deleting the comment...'));
	}

	protected function processViewImage()
	{
		$id_image = (int)Tools::getValue('id_image');
		$image = new Image($id_image);
		$image_feed = '';
		if ($image->isLoadedObject())
		{
			$image_feed = $this->fetchImageFeed($image);
		}
		$this->addCSS('css/gallery.css');
		$this->addJS('js/gallery.js');
		$tpl = new Tpl();
		$tpl->assign("domain_link", _DOMAIN_);
		$tpl->assign("js_files", $this->fetchJS());
		$tpl->assign("css_files", $this->fetchCSS());
		$tpl->assign("image_feed", $image_feed);
		$tpl->assign("id_image", $id_image);
		if ($this->user->isLoadedObject())
		{
			$comment_form_tpl = new Tpl();
			$comment_form_tpl->assign("domain_link", _DOMAIN_);
			$comment_form_tpl->assign('id_image', $id_image);
			$tpl->assign("comment_form", $comment_form_tpl->fetch('gallery/helpers/comment_form.html'));
			$tpl->assign("logout_button", '<a href="'._DOMAIN_.'workshop"><button id="workshop">workshop</button></a><button id="logout">logout</button>');
		}
		else
		{
			$tpl->assign("comment_form", '');
			$tpl->assign("logout_button", '<a href="'._DOMAIN_.'login"><button id="login">login</button>');
		}
		echo $tpl->fetch("gallery/view.html");
	}
}