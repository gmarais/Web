<?php

abstract class Controller
{
	protected $action;

	abstract protected function checkToken();

	protected function setTranslation()
	{
		if ($lang = Tools::getValue("lang"))
		{
			Translation::setLanguage($lang);
		}
	}

	protected function _t($key)
	{
		return Translation::translate($key, get_class($this));
	}

	abstract protected function render();

	protected function getAction()
	{
		$this->action = Tools::getValue("action");
		$called_method = "process".$this->action;
		if (method_exists($this, $called_method))
		{
			return $this->$called_method();
		}
		return $this->render();
	}

	public function run()
	{
		$this->checkToken();
		$this->setTranslation();
		$this->getAction();
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
		if (!preg_match('/[\W]/', $helper_function_name) && method_exists($this, $config_function_name))
		{
			$this->$config_function_name($tpl);
		}
		echo $tpl->fetch($template_name);
	}
}