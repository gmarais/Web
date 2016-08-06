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

class Translation
{
	static private $_translation_tab = null;
	static private $_current_lang = "en";

	static private function parseDirectoriesRecursively($dirname, $handle)
	{
		$existing_keys;
		while (false !== ($entry = readdir($handle)))
		{
			if ($entry != "." && $entry != ".." && $entry != "manifest.txt")
			{
				if (is_dir($dirname."/".$entry) && $newHandle = opendir($dirname."/".$entry))
				{
					self::parseDirectoriesRecursively($dirname."/".$entry, $newHandle);
				}
				else
				{
					$existing_keys = array();
					$tmp = array("" => "");
					preg_match_all('/(?:\$this->_t\("((?:[^\\"]|\\.)*)"\))|(?:\$this->_t\(\'((?:[^\\\']|\\.)*)\'\))/', file_get_contents($dirname."/".$entry), $existing_keys);
					$matches = array_merge($existing_keys[1],$existing_keys[2]);
					foreach ($matches as $key)
					{
						$tmp[$key] = $key;
					}
					self::$_translation_tab[ucfirst(str_replace(".php", "", $entry))] = $tmp;
				}
			}
		}
		closedir($handle);
	}

	static private function createLanguageFile($file)
	{
		if (is_dir(_CONTROLLERS_DIR_))
		{
			if ($handle = opendir(_CONTROLLERS_DIR_))
			{
				self::parseDirectoriesRecursively(_CONTROLLERS_DIR_, $handle);
				$file_handle = fopen($file, "w");
				fwrite($file_handle, json_encode(self::$_translation_tab, JSON_PRETTY_PRINT));
				fclose($file_handle);
			}
		}
	}

	static public function setLanguage($language)
	{
		$possible_lang = array("en", "fr", "ru");
		if (in_array($language, $possible_lang))
		{
			self::$_current_lang = $language;
		}
		if (strcmp(self::$_current_lang, "en") == 0)
		{
			self::$_translation_tab = array();
			return;
		}
		if (file_exists(_TRANSLATIONS_DIR_."/".self::$_current_lang.".json") == false) {
			self::createLanguageFile(_TRANSLATIONS_DIR_."/".self::$_current_lang.".json");
		}
		self::$_translation_tab = json_decode(file_get_contents(_TRANSLATIONS_DIR_."/".self::$_current_lang.".json"), true);
		if (!self::$_translation_tab)
			self::$_translation_tab = array();
	}

	static public function getCurrentLang()
	{
		return self::$_current_lang;
	}

	static public function translate($key, $version = "generic")
	{
		if (self::$_translation_tab === null)
		{
			self::setLanguage("en");
			return self::translate($key, $version);
		}
		else if (array_key_exists($version, self::$_translation_tab) && array_key_exists($key, self::$_translation_tab[$version]))
		{
			return self::$_translation_tab[$version][$key];
		}
		else if (self::$_translation_tab !== null && array_key_exists($version, self::$_translation_tab) && !array_key_exists($key, self::$_translation_tab[$version]))
		{
			self::$_translation_tab[$version][$key] = $key;
			$file_handle = fopen(_TRANSLATIONS_DIR_."/".self::$_current_lang.".json", "w");
			fwrite($file_handle, json_encode(self::$_translation_tab, JSON_PRETTY_PRINT));
			fclose($file_handle);
		}
		return $key;
	}
}