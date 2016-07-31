<?php

class HomeController extends Controller
{
	protected function checkToken()
	{
	}

	protected function render()
	{
		$tpl = new Tpl();
		echo $tpl->fetch("home.html");
	}
}