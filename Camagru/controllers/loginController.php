<?php

class LoginController extends Controller
{
	protected function checkToken()
	{
		$cookie = new Cookie('camagru');
		if ($cookie->exists())
		{
			$user = new User((int)$cookie->id_user);
			if ($user->isLoadedObject())
			{
				Tools::redirect('/');
			}
		}
	}

	protected function render()
	{
		$this->addJS('js/login.js');
		$tpl = new Tpl();
		$tpl->assign("domain_link", _DOMAIN_);
		$tpl->assign("js_files", $this->fetchJS());
		$tpl->assign("css_files", $this->fetchCSS());
		$tpl->assign("logout_button", '');
		echo $tpl->fetch("login/login.html");
	}

	protected function processValidate()
	{
		$key = Tools::getValue('key');
		$key_decrypted = base64_decode(urldecode($key));
		$credentials = json_decode($key_decrypted, true);
		if (is_array($credentials)
			&& array_key_exists('id_user', $credentials)
			&& array_key_exists('key_hash', $credentials)
			&& array_key_exists('date', $credentials))
		{
			$user = new User($credentials['id_user']);
			if ($user->isLoadedObject() && $user->active == false)
			{
				if ($user->key_hash == $credentials['key_hash']
					&& $user->registration_date == $credentials['date'])
				{
					$user->active = true;
					if ($user->save())
					{
						$this->addJS('js/login.js');
						$tpl = new Tpl();
						$tpl->assign("domain_link", _DOMAIN_);
						$tpl->assign('nickname', $user->nickname);
						$tpl->assign("js_files", $this->fetchJS());
						$tpl->assign("css_files", $this->fetchCSS());
						$tpl->assign("logout_button", '');
						echo $tpl->fetch("login/welcome.html");
						return;
					}
				}
			}
		}
		$this->addJS('js/login.js');
		$tpl = new Tpl();
		$tpl->assign("domain_link", _DOMAIN_);
		$tpl->assign("logout_button", '');
		$tpl->assign("js_files", $this->fetchJS());
		$tpl->assign("css_files", $this->fetchCSS());
		echo $tpl->fetch("login/validationFail.html");
	}

	protected function processRegister()
	{
		$nickname = Tools::getValue('nickname');
		$password = Tools::getValue('password');
		$email = filter_var(Tools::getValue('email'), FILTER_VALIDATE_EMAIL);
		if (strlen($nickname) < 3
			|| !$email
			|| strlen($password) < 6
			|| !preg_match("/\d\D|\D\d/", $password))
		{
			echo new JsonResponse('KO', array('message' => 'Provided credentials are not ok...'));
			return;
		}
		if (User::loadByNickname($nickname)->isLoadedObject())
		{
			echo new JsonResponse('KO', array('message' => 'We are sorry that nickname is already used...'));
			return;
		}
		if (User::loadByEmail($email)->isLoadedObject())
		{
			echo new JsonResponse('KO', array('message' => 'We are sorry that email is already used...'));
			return;
		}
		$user = new User();
		$user->nickname = $nickname;
		$user->email = $email;
		$user->key_hash = MD5(time() . $user->nickname . _SECURE_KEY_);
		$user->password = MD5(_SECURE_KEY_ . $password . $user->key_hash);
		$user->registration_date = date('Y-m-d H:i:s');
		if ($user->save())
		{
			$mail = new Mail('Welcome to Camagru '.$user->nickname, $user->email, 'mailValidation');
			$mail->assign("domain_link", _DOMAIN_);
			$mail->assign('companyName', 'Camagru');
			$mail->assign('nickname', $user->nickname);
			$validation_key = urlencode(base64_encode('{"id_user":"'.$user->id.'", "key_hash":"'.$user->key_hash.'", "date":"'.$user->registration_date.'"}'));
			$mail->assign('validationLink', _DOMAIN_.'login?action=validate&key='.$validation_key);
			$mail->send();
			echo new JsonResponse('OK');
		}
		else
		{
			echo new JsonResponse('KO', array('message' => 'An error occured creating your account please try filling the form again...'));
		}
	}

	protected function processLogin()
	{
		$nickname = Tools::getValue('nickname');
		$password = Tools::getValue('password');
		$user = User::loadByNickname($nickname);
		if ($user->isLoadedObject() && $user->checkPassword($password))
		{
			if (!$user->active)
			{
				echo new JsonResponse('KO', array('message' => 'User account is not active...'));
				return;
			}
			$cookie = new Cookie("camagru");
			$cookie->id_user = $user->id;
			echo new JsonResponse('OK');
			return;
		}
		echo new JsonResponse('KO', array('message' => 'User not found or password is invalid...'));
	}

	protected function processLogout()
	{
		$cookie = new Cookie("camagru");
		$cookie->delete();
		Tools::redirect('/');
	}
}