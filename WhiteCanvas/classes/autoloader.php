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

/* register our ClassLoader as the default class loader */
spl_autoload_register(array(ClassLoader::getInstance(), 'loadClass'));

class ClassLoader {
	private static $saved_list_path;
	/* singleton */
	private static $instance;

	/* stores a className -> filePath map */
	private $class_list;
	/* tells whether working from saved file */
	private $refreshed;

	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new ClassLoader();
		}
		return self::$instance;
	}

	private function __construct()
	{
		self::$saved_list_path = _CACHE_DIR_.'/ClassLoaderList.php';
		if (is_dir(_CACHE_DIR_) == false)
			@mkdir(_CACHE_DIR_, 0755);
		$this->initClassList();
	}

	public function loadClass($class_name)
	{
		$class_name = strtolower($class_name);
		if ((!$this->class_list || !array_key_exists($class_name, $this->class_list)) && !$this->refreshed) {
			$this->refreshClassList();
			$this->loadClass($class_name);
		} else if (!empty($this->class_list[$class_name])) {
			require_once($this->class_list[$class_name]);
		}
	}

	private function initClassList()
	{
		if (file_exists(self::$saved_list_path)) {
			require_once(self::$saved_list_path);
			$this->refreshed = false;
		} else {
			$this->refreshClassList();
		}
	}

	private function refreshClassList()
	{
		$this->class_list = array_merge(
			$this->scanDirectory(_CLASSES_DIR_),
			$this->scanDirectory(_CONTROLLERS_DIR_),
			$this->scanDirectory(_DB_OBJECTS_DIR_)
		);
		$this->refreshed = true;
		$this->saveClassList();
	}

	private function saveClassList()
	{
		$handle = fopen(self::$saved_list_path, 'w');
		if (!$handle)
			return;
		fwrite($handle, "<?php\n".'$this'."->class_list = array(\n");
		foreach($this->class_list as $class => $path) {
			$line = "\t'".$class."' => '". $path ."',\n";
			fwrite($handle, $line);
		}
		fwrite($handle, ");\n?>\n");
		fclose($handle);
	}

	private function scanDirectory($directory)
	{
		// strip closing '/'
		if (substr($directory, -1) == '/') {
			$directory = substr($directory, 0, -1);
		}
		if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
			return array();
		}

		$directory_fd = opendir($directory);
		$scan_result = array();
		while(($file = readdir($directory_fd)) !== false) {
			// skip pointers
			if ( strcmp($file , '.') == 0 || strcmp($file , '..') == 0) {
				continue;
			}
			$path = $directory . '/' . $file;
			if (!is_readable($path)) {
				continue;
			}
			// recursion
			if (is_dir($path)) {
				$scan_result = array_merge($scan_result, $this->scanDirectory($path));
			} else if (is_file($path) && preg_match('/.php$/', $file)) {
				$class_name = preg_replace('/.php$/', '', $file);
				$class_name = strtolower($class_name);
				$scan_result[$class_name] = $path;
			}
		}
		return $scan_result;
	}
}
