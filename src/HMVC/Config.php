<?php

class HMVC_Config extends CI_Config 
{
	public function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE) {
		
		// first we need to check if this is called before CI_controller
		// if yes, well just use default
		if(!class_exists('CI_controller'))
		{
			return parent::load($file, $use_sections, $fail_gracefully);		
		}

		if (in_array($file, $this->is_loaded, TRUE))
		{
			return $this->item($file);
		}

		$_module = Modules::$current;
		
		if($path = modules::find($file, $_module, 'config'))
		{
			// this file expected contain $config var
			include $path;

			if ( ! isset($config) OR ! is_array($config))
			{
				show_error("{$path} does not contain a valid config array");
			}

			log_message('debug', "File loaded: {$path}");

			$current_config =& $this->config;

			if ($use_sections === TRUE)	{
				
				if (isset($current_config[$file])) {
					$current_config[$file] = array_merge($current_config[$file], $config);
				} else {
					$current_config[$file] = $config;
				}
				
			} else {
				$current_config = array_merge($current_config, $config);
			}

			$this->is_loaded[] = $file;
			unset($config);
			return $this->item($file);
		}
		
		return parent::load($file, $use_sections, $fail_gracefully);		
	}
}