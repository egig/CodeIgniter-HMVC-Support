<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Modules
{
	public static $paths = array(), $routes, $installed, $loaded, $current;

	/**
	 * Get module paths
	 *
	 * @return array
	 */
	public static function get_paths()
	{
		return static::$paths;
	}

	/**
	 * Add paths
	 *
	 * @param string|array $path
	 */
	public static function add_path($path)
	{
		static::$paths = array_merge(static::$paths,(array)$path);
	}

	/** Load a module controller **/
	public static function load($module, $controller = '')
	{
		static::$current = $module;

		if(! array_key_exists($module, static::get_installed()))
		{
			show_error("Module $module not installed");
		}

		if($controller !== '')
		{
			if(!isset(static::$loaded[$module]['controllers'][$controller]))
			{
				require_once static::$installed[$module].'/controllers/'.ucfirst($controller).'.php';
				static::$loaded[$module]['controllers'][$controller] = new $controller;
			}
			return static::$loaded[$module]['controllers'][$controller];
		}

		if(!isset(static::$loaded[$module]))
		{
			require_once static::$installed[$module].'/src/'.ucfirst($module).'.php';
			static::$loaded[$module] = new $module;
		}

		return static::$loaded[$module];
	}

	/**
	 * Find a file on a modules directories
	 *
	 * @param string $file
	 * @param string $module
	 * @param string $base
	 * @return string
	 */
	public static function find($file, $module, $base = 'libraries') {

		$segments = explode('/', $file);

		$file = array_pop($segments);
		$file_ext = strpos($file, '.') ? $file : $file.'.php';
		
		$path = ltrim(implode('/', $segments).'/', '/');	
		$module ? $modules[$module] = $path : $modules = array();

		if ( ! empty($segments)) {
			$modules[array_shift($segments)] = ltrim(implode('/', $segments).'/','/');
		}

		foreach (static::$paths as $path) {

			foreach($modules as $module => $subpath) {		
				$fullpath = $path.'/'.$module.'/'.trim($base,'/').'/'.$subpath;		

				// if file intended is a class, we'll look for ucfirst-named file
				if (in_array($base, array('models', 'libraries'))
					 AND is_file($found = $fullpath.ucfirst($file_ext))) {
						return $found;
				}
					
				if (is_file($found = $fullpath.$file_ext)) {
					return $found;
				}
			}
		}

		return false;
	}

	/**
	 * Get assoc array of installed module name and their path.
	 *
	 * @return arrays
	 */
	public static function get_installed()
	{
		if(static::$installed) {
			return static::$installed;
		}

		$data = array();
		foreach (static::$paths as $path) {

			$files = new \FileSystemIterator($path);

			foreach ($files as $file) {
				$data[$file->getFileName()] = $file->getRealPath();
			}
		}

		return static::$installed = $data;
	}
}