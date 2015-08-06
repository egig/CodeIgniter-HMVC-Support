<?php

class HMVC_Loader extends CI_Loader {

	private $loaded_files = array();

	public function __construct()
	{
		parent::__construct();
		$this->_module = Modules::$current;
	}

	/** Override ci view paths
	 *
	 * @param string $path
	 * @param boolean $cascade
	 * @return void
	 */
	public function set_ci_view_path($path, $cascade = TRUE)
	{
		$path = rtrim($path, '/').DIRECTORY_SEPARATOR;
		$this->_ci_view_paths = array($path => $cascade);
	}
	
	public function model($model, $object = NULL, $connect = FALSE) {

		if(is_array($model)) {
			return $this->models($model);
		}

		$object OR $object = basename($model);

		if (in_array($object, $this->_ci_models, TRUE)) {
			return get_instance()->$object;
		}

		if($path = modules::find(strtolower($model), $this->_module, 'models')) {

			class_exists('CI_Model', FALSE) OR load_class('Model', 'core');
			
			if ($connect !== FALSE AND ! class_exists('CI_DB', FALSE)) {
				if ($connect === TRUE) $connect = '';
				$this->database($connect, FALSE, TRUE);
			}

			if(!in_array($path, $this->loaded_files)) {
				require_once $path;
			}
			
			$class = ucfirst(basename($model));
			get_instance()->$object = new $class();
			
			$this->_ci_models[] = $object;
			
			return get_instance()->$object;
		}
		
		return parent::model($model, $object);
	}

	public function models($models) {
		foreach ($models as $_model) $this->model($_model);	
	}

	public function helper($helper = array()) {
		
		if (is_array($helper)) return $this->helpers($helper);
		
		if (isset($this->_ci_helpers[$helper]))	return;

		$path = modules::find($helper.'_helper', $this->_module, 'helpers');

		if ($path === FALSE) return parent::helper($helper);

		
		if(!in_array($path, $this->loaded_files)) {
			require $path;
		}

		$this->_ci_helpers[$helper] = TRUE;
	}

	public function helpers($helpers = array()) {
		foreach ($helpers as $_helper) $this->helper($_helper);	
	}

	public function library($library = '', $params = NULL, $object_name = NULL) {

		if (is_array($library))
		{
			return $this->libraries($library);
		}

		$class = ucfirst(basename($library));

		$object_name or $object_name = strtolower($class);

		if (isset($this->_ci_classes[$object_name])
			AND  $this->_ci_classes[$object_name] == $class)
		{
			return get_instance()->$object_name;
		}
		
		$path = Modules::find($library, $this->_module, 'libraries');

		// load library config file as params
		if ($params == NULL)
		{
			$path2 = Modules::find(strtolower($class), $this->_module, 'config');

			if($path2)
			{
				if(!in_array($path, $this->loaded_files))
				{
					require $path;
				}
			}
		}

		if ($path === FALSE)
		{
			$this->_ci_load_library($library, $params, $object_name);

		}
		else
		{
			if(!in_array($path, $this->loaded_files))
			{
				require_once $path;
			}

			//$this->_ci_classes[$object_name] = $class;
			get_instance()->$object_name = new $class($params);
		}
		
		return get_instance()->$object_name;
    }

	public function libraries($libraries) {
		foreach ($libraries as $_library) $this->library($_library);	
	}

	public function view($view, $vars = array(), $return = FALSE) {

		if($path = modules::find($view, $this->_module, 'views')) 
		{
			return $this->_ci_load(array('_ci_path' => $path, '_ci_vars' => $vars, '_ci_return' => $return));
		}
		
		return parent::view($view, $vars, $return);
	}
}