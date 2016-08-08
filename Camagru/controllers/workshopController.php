<?php

class WorkshopController extends Controller
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
		Tools::redirect('/login');
	}

	protected function render()
	{
		$masks = scandir('images/masks');
		$masks_lines = '';
		foreach ($masks as $mask)
		{
			if (preg_match('/.png$/', $mask))
			{
				$masks_lines .= '<img data-id="'.urlencode(Encryption::simpleEncrypt($mask)).'" src="images/masks/'.$mask.'" class="mask_button wc-xs-3 wc-sm-2 wc-md-2 wc-lg-2" />';
			}
		}
		$this->addJS('js/camera.js');
		$this->addJS('js/workshop.js');
		$this->addCSS('css/workshop.css');
		$tpl = new Tpl();
		$tpl->assign("domain_link", _DOMAIN_);
		$tpl->assign("masks_lines", $masks_lines);
		$tpl->assign("userName", $this->user->nickname);
		$tpl->assign("js_files", $this->fetchJS());
		$tpl->assign("css_files", $this->fetchCSS());
		$tpl->assign("logout_button", '<button id="logout">logout</button>');
		echo $tpl->fetch("workshop/workshop.html");
	}

	protected function processGetLastMixedImages()
	{
		$mixed_images_list = '';
		$images = Image::loadImages(14, 0, $this->user->id);
		foreach ($images as $image)
		{
			$mixed_images_list .= '<img class="last_images wc-xs-3" data-id="'.$image->id.'" src="'.$image->src.'">'."\n";
		}
		echo new JsonResponse('OK', array('images_list' => $mixed_images_list));
	}

	protected function processDeleteImage()
	{
		$id_image = Tools::getValue('id_image');
		$image = new Image($id_image);
		if ($image->isLoadedObject() && $this->user->id == $image->id_user && $image->delete())
		{
			unlink($image->src);
			if (Tools::isSubmit('getList'))
				$this->processGetLastMixedImages();
			else
				echo new JsonResponse('OK');
		}
		else
		{
			echo new JsonResponse('KO', array('message' => 'An error occured deleting the image...'.$id_image));
		}
	}

	protected function processMixImages()
	{
		$raw_photo = Tools::getValue('raw_photo');
		$raw_photo = substr($raw_photo, strpos($raw_photo, ",") + 1);
		$raw_photo = str_replace(' ', '+', $raw_photo);
		$raw_photo = base64_decode($raw_photo);
		$mask = Tools::getValue('mask');
		$mask = Encryption::simpleDecrypt(urldecode($mask));
		$dest = imagecreatefromstring($raw_photo);
		if ($mask && ($mask = imagecreatefrompng('images/masks/'.$mask)))
		{
			imagealphablending($dest, true);
			imagesavealpha($dest, true);
			imagecopy($dest, $mask, 0, 0, 0, 0, 800, 600);
			imagedestroy($mask);
		}
		$image_name = MD5($this->user->id.$this->user->key_hash.time());
		if (!is_dir('images/db'))
			@mkdir('images/db', 0755, true);
		imagepng($dest, 'images/db/'.$image_name.'.png');
		imagedestroy($dest);
		$image = new Image();
		$image->src = 'images/db/'.$image_name.'.png';
		$image->id_user = $this->user->id;
		$image->date = date('Y-m-d H:i:s');
		if ($image->save())
			$this->processGetLastMixedImages();
		else
			echo new JsonResponse('KO', array('message' => 'An error occured saving the image...'));
	}
}