<?php

class HMVC_Loader extends CI_Loader {

	private $loaded_files = array();

	public function __construct()
	{
		parent::__construct();
		$this->_module = Modules::$current;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _ci_load_library($class, $params = NULL, $object_name = NULL)
	{
		$class = $this->_parse_path($class, $this->_ci_library_paths);

		return parent::_ci_load_library($class, $params, $object_name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function model($model, $object = NULL, $connect = FALSE)
	{
		$model = $this->_parse_path($model, $this->_ci_model_paths);

		return parent::model($model, $object, $connect);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _ci_prep_filename($filename, $extension)
	{
		if($extension !== '_helper')
		{
			return parent::_ci_prep_filename($filename, $extension);
		}

		if ( ! is_array($filename))
		{
			$filename = array(strtolower(str_replace(array($extension, '.php'), '', $filename).$extension));
		}
		else
		{
			foreach ($filename as $key => $val)
			{
				$filename[$key] = strtolower(str_replace(array($extension, '.php'), '', $val).$extension);
			}
		}

		foreach ($filename as $key => $fn)
		{
			$filename[$key] = $this->_parse_path($fn, $this->_ci_helper_paths);
		}

		return $filename;
	}

	/**
	 * {@inheritdoc}
	 */
	public function view($view, $vars = array(), $return = FALSE)
	{		
		$view = $this->_parse_path($view, $this->_ci_view_paths, TRUE);

		return parent::view($view, $vars, $return);
	}

	/**
	 * Override ci view paths
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

	/**
	 * Add prepend module path.
	 *
	 * @param string $path
	 * @param string $merge_path
	 * @param boolean $view
	 * @return string
	 */
	private function _parse_path($path, &$merge_path, $view = FALSE)
	{
		if(is_string($path) and $path[0] == '@')
		{
			$_temp = explode('/', $path);

			$module = array_shift($_temp);
			$module_path = Modules::get_path(substr($module, 1));

			if($view)
			{
				$merge_path = array($module_path.'/views/' => TRUE) + $merge_path;
			}
			else
			{			
				// add module path, mind the trailing slash
				array_unshift($merge_path, $module_path.'/');
			}

			$path = implode('/', $_temp);
		}

		return $path;
	}
}