<?php

class HomeController extends Controller
{
	protected function render()
	{
		$this->addCSS('css/wc.css');
		$this->addJS('js/wc.js');
		$tpl = new Tpl();
		$tpl->assign('css_files', $this->fetchCSS());
		$tpl->assign('js_files', $this->fetchJS());
		echo $tpl->fetch("home.html");
	}
}