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

class CacheLoader
{
	static public function load()
	{
		if (defined('_CACHE_')
			&& $_SERVER['REQUEST_METHOD'] == 'GET'
			&& empty($_GET)
			&& empty($_POST)
			&& isset($_SERVER['HTTP_USER_AGENT'])
			&& strpos($_SERVER['REQUEST_URI'], _ROOT_DIR_.'feed/') == false
			&& strpos($_SERVER['REQUEST_URI'], _ROOT_DIR_.'office') == false
		)
		{
			$lang = "en";
			if (Session::has('lang'))
			{
				$lang = Session::get('lang');
			}
			else
			{
				$locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
				if (substr_count($locale, "fr") > 0)
				{
					$lang = "fr";
				}
				else if (substr_count($locale, "ru") > 0)
				{
					$lang = "ru";
				}
			}

			$wantedCacheFolder = _CACHE_DIR_.'/'.$lang.$_SERVER['REQUEST_URI'];
			if (!file_exists($wantedCacheFolder.'index.html.gz'))
			{
				$data = file_get_contents(_DOMAIN_.$_SERVER['REQUEST_URI']."?lang=".$lang);
				if (!is_dir($wantedCacheFolder))
					@mkdir($wantedCacheFolder, 0755, true);
				file_put_contents($wantedCacheFolder.'index.html.gz', gzencode($data, 9));
			}
			else
			{
				readgzfile($wantedCacheFolder.'index.html.gz');
				exit;
			}
		}
	}
}
