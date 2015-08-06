<?php

class HMVC_Lang extends CI_Lang
{
	public function load($langfile, $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '')
	{
		// first we need to check if this is called before CI_controller
		// if yes, well just use default
		if(!class_exists('CI_controller'))
		{
			return parent::load($file, $use_sections, $fail_gracefully);		
		}

		if (is_array($langfile)) {
			foreach($langfile as $_lang) $this->load($_lang);
			return $this->language;
		}
			
		$deft_lang = get_instance()->config->item('language');
		$idiom OR $idiom = $deft_lang;
	
		if (in_array($langfile.'_lang.php', $this->is_loaded, TRUE))
			return $this->language;

		$_module = get_instance()->router->fetch_module();
		
		if($path = modules::find($langfile.'_lang', $_module, 'language/'.$idiom))
		{
			include $path;

			if ( ! isset($lang) OR ! is_array($lang))
			{
				show_error("{$path} does not contain a valid lang array");
			}

			if($return) {
				return $lang;
			}

			$this->language = array_merge($this->language, $lang);
			$this->is_loaded[] = $langfile.'_lang.php';
			unset($lang);

			return $this->language;
		}

		return parent::load($langfile, $idiom, $return, $add_suffix, $alt_path);
	}
}