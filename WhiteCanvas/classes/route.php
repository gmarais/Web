<?php

class Route
{
	private $_uri_method;

	public function __construct()
	{
		$routes = file_get_contents(_CONFIG_DIR_."/routes.json");
		$routes = json_decode($routes, true);
		if (is_array($routes))
		{
			foreach ($routes as $key => $value)
			{
				$this->_uri_method[_ROOT_DIR_.trim($key, '/')] = $value;
			}
		}
	}

	public function add($uri, $method = null)
	{
		if ($method != null)
		{
			$this->_uri_method[_ROOT_DIR_.trim($uri, '/')] = $method;
		}
	}

	public function submit()
	{
		$uri_get_param = isset($_GET['uri']) ? _ROOT_DIR_.$_GET['uri'] : _ROOT_DIR_;
		foreach ($this->_uri_method as $key => $controller)
		{
			if (preg_match("#^$key$#", $uri_get_param))
			{
				return (new $controller())->run();
			}
		}
		header('Location: /'._ROOT_DIR_.'error404');
	}
}
