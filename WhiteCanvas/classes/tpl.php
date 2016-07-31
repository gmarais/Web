<?php

class Tpl
{
	private $_content;
	private $_vars;

	public function assign($key, $value)
	{
		$this->_vars[$key] = $value;
	}

	public function getTemplateFileContentByName($templateName)
	{
		$content = '';
		if (file_exists(_TEMPLATES_DIR_.'/'.$templateName))
		{
			$content = file_get_contents(_TEMPLATES_DIR_.'/'.$templateName);
		}
		else if (file_exists(_TEMPLATES_DIR_.'/'.Translation::getCurrentLang().'/'.$templateName))
		{
			$content = file_get_contents(_TEMPLATES_DIR_.'/'.Translation::getCurrentLang().'/'.$templateName);
		}
		else if (file_exists($templateName))
		{
			$content = file_get_contents($templateName);
		}
		return $content;
	}

	public function fetch($tplName)
	{
		//Add includes
		$this->_content = $this->getTemplateFileContentByName($tplName);
		while (preg_match("/(?:{\s*include\s*file\s*=\s*[\"'](.*)[\"']\s*})/i", $this->_content, $match))
		{
			$this->_content = preg_replace("/{\s*include\s*file\s*=\s*[\"']".Tools::escapeSlashes($match[1])."[\"']\s*}/i", $this->getTemplateFileContentByName($match[1], false, true), $this->_content);
		}
		//Set variales
		if (!is_array($this->_vars))
			return $this->_content;
		foreach ($this->_vars as $key => $value)
		{
			$this->_content = preg_replace("/{[\s]*[$]".$key."[\s]*}/", $value, $this->_content);
		}
		return $this->_content;
	}
}
