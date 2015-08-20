<?php

class HMVC_Router extends CI_Router {

	/**
	 * The module
	 *
	 * @var array
	 */
	protected $module;

	/**
	 * Routing defined in index.php
	 *
	 * @var array
	 */
	protected $index_routing;

	/**
	 * Class constructor
	 *
	 * Runs the route mapping function.
	 *
	 * @return	void
	 */
	public function __construct($routing = NULL)
	{
		$this->index_routing = $routing;

		parent::__construct($routing);
	}

	public function fetch_module() {
		return $this->module;
	}

	protected function _validate_request($segments)
	{
		/* locate module controller */
		if ($located = $this->locate($segments)) {
			return $located;
		}

		/* use a default 404_override controller */
		if (isset($this->routes['404_override']) AND $this->routes['404_override']) {
			$segments = explode('/', $this->routes['404_override']);
			if ($located = $this->locate($segments)) return $located;
		}

		return parent::_validate_request($segments);
	}

	/**
	 * Locate segments against modules, re-route the directory
	 *
	 * @return string $segments
	 */
	public function locate($segments)
	{
		// we do nothing if routing defined in index.php
		if($this->index_routing) {
			return;
		}

		$paths = Modules::$paths;
		$ext = $this->config->item('controller_suffix').'.php';

		//@todo user modules routes config

		/* get the segments array elements */
		list($segment1, $segment2, $segment3) = array_pad($segments, 3, NULL);

		/* check modules */
		foreach ($paths as $path) {

			$path = rtrim($path).'/';
			$path = static::make_path_relative(APPPATH.'controllers', $path);


			// We need a private modules which is can't be accessed via uri
			// so, any uri started with underscore will throws 404
			// pretty good idea, huh ?
			if($segment1[0] === '_') {
				show_404();
			}

			 // Due to compatibility with codeigniter,
			 // Modules path checked is relative to app/controller directory
			 // I told you to just use symfony (-__-");
			if (is_dir($source = APPPATH.'controllers/'.$path.$segment1.'/controllers/')) {
				
				$this->module = $segment1;
				$this->directory = $path.$segment1.'/controllers/';

				/* module sub-controller exists? */
				if($segment2 AND is_file($source.ucfirst($segment2).$ext)) {
					return array_slice($segments, 1);
				}
					
				/* module sub-directory exists? */
				if($segment2 AND is_dir($source.$segment2.'/')) {

					$source .= $segment2.'/';
					$this->directory .= $segment2.'/';

					/* module sub-directory controller exists? */
					if(is_file($source.ucfirst($segment2).$ext)) {
						return array_slice($segments, 1);
					}
				
					/* module sub-directory sub-controller exists? */
					if($segment3 AND is_file($source.ucfirst($segment3).$ext))	{
						return array_slice($segments, 2);
					}
				}
				
				/* module controller exists? */			
				if(is_file($source.ucfirst($segment1).$ext)) {
					return $segments;
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _set_default_controller()
	{
		if (empty($this->default_controller))
		{
			show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
		}

		// Is the method being specified?
		if (sscanf($this->default_controller, '%[^/]/%s', $class, $method) !== 2)
		{
			$method = 'index';
		}

		$this->set_class($class);
		$this->set_method($method);

		// Assign routed segments, index starting from 1
		$this->uri->rsegments = array(
			1 => $class,
			2 => $method
		);

		$this->locate($this->uri->rsegments);

		log_message('debug', 'No URI present. Default controller set.');
	}

	public static function make_path_relative($from, $to)
	{
	    // some compatibility fixes for Windows paths
	    $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
	    $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
	    $from = str_replace('\\', '/', $from);
	    $to   = str_replace('\\', '/', $to);

	    $from     = explode('/', $from);
	    $to       = explode('/', $to);
	    $relPath  = $to;

	    foreach($from as $depth => $dir) {
	        // find first non-matching dir
	        if($dir === $to[$depth]) {
	            // ignore this directory
	            array_shift($relPath);
	        } else {
	            // get number of remaining dirs to $from
	            $remaining = count($from) - $depth;
	            if($remaining > 1) {
	                // add traversals up to first matching dir
	                $padLength = (count($relPath) + $remaining - 1) * -1;
	                $relPath = array_pad($relPath, $padLength, '..');
	                break;
	            } else {
	                $relPath[0] = './' . $relPath[0];
	            }
	        }
	    }
	    return implode('/', $relPath);
	}

	/**
	 * Set directory name
	 *
	 * @param	string	$dir	Directory name
	 * @param	bool	$appent	Whether we're appending rather then setting the full value
	 * @return	void
	 */
	public function set_directory($dir, $append = FALSE)
	{
		if ($append !== TRUE OR empty($this->directory))
		{
			$this->directory = trim($dir, '/').'/';
		}
		else
		{
			$this->directory .= trim($dir, '/').'/';
		}
	}
}