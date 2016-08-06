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

abstract class Controller
{
	protected $action;
	private $_js = array();
	private $_css = array();

	abstract protected function checkToken();
	abstract protected function render();

	public function run()
	{
		$this->checkToken();
		$this->setTranslation();
		$this->getAction();
	}

	private function setTranslation()
	{
		if ($lang = Tools::getValue("lang"))
		{
			Translation::setLanguage($lang);
		}
	}

	private function getAction()
	{
		$this->action = Tools::getValue("action");
		$called_method = "process".$this->action;
		if (method_exists($this, $called_method))
		{
			return $this->$called_method();
		}
		return $this->render();
	}

	protected function _t($key)
	{
		return Translation::translate($key, get_class($this));
	}

	protected function addJS($file)
	{
		if (is_array($file))
		{
			$this->_js = array_merge($this->_js, $file);
		}
		else if (in_array($file, $this->_js) == false)
		{
			$this->_js[] = $file;
		}
	}

	protected function addCSS($file)
	{
		if (is_array($file))
		{
			$this->_css = array_merge($this->_css, $file);
		}
		else if (in_array($file, $this->_css) == false)
		{
			$this->_css[] = $file;
		}
	}

	protected function fetchJS()
	{
		$str = '';
		foreach ($this->_js as $value)
		{
			$str .= '<script src="'.$value.'" type="text/javascript"></script>'."\n";
		}
		return $str;
	}

	protected function fetchCSS()
	{
		$str = '';
		foreach ($this->_css as $value)
		{
			$str .= '<link rel="stylesheet" type="text/css" href="'.$value.'"></script>'."\n";
		}
		return $str;
	}

	/* The config function is called to configure the helper tpl variables if there are
	 * Helpers are simple templates parts to be loaded dynamicly
	 */
	protected function processRenderHelper()
	{
		$helper_name = Tools::getValue('helper_name');
		$template_name = strtolower(preg_replace('/Controller$/', '', get_class($this)))
			.'/helpers/'.$helper_name;
		$tpl = new Tpl();
		$config_function_name = 'config'.preg_replace('/[.]html$/', '', $helper_name).'Helper';
		if (!preg_match('/[\W]/', $config_function_name) && method_exists($this, $config_function_name))
		{
			$this->$config_function_name($tpl);
		}
		echo $tpl->fetch($template_name);
	}
}