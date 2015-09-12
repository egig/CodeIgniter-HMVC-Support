<?php

class HMVC_Input extends CI_Input 
{
	/**
	 * mock $_GET data for test.
	 *
	 * @var array
	 */
	private $mock_GET = [];

	/**
	 * mock $_POST data for test.
	 *
	 * @var array
	 */
	private $mock_POST = [];

	/**
	 * mock $_COOKIE data for test.
	 *
	 * @var array
	 */
	private $mock_COOKIE = [];

	/**
	 * mock $_SERVER data for test.
	 *
	 * @var array
	 */
	private $mock_SERVER = [];

	/**
	 * mock state.
	 *
	 * @var boolean
	 */
	private $mock_state = false;

	/**
	 * Enable mock state;
	 *
	 * @return void;
	 */
	public function enable_mock_state()
	{
		$this->mock_state = true;	
	}

	/**
	 * Disable mock state.
	 *
	 * @return void;
	 */
	public function disable_mock_state()
	{
		$this->mock_state = false;
	}

	/**
	 * Set mock data.
	 *
	 * @param string 'GET|POST|SERVER|COOKIE' $global
	 * @param mixed $data
	 */
	public function set_mock_data($global, $data)
	{
		$mock_property = 'mock_'.strtoupper($global);

		$this->$mock_property = $data;
	}

	/**
	 * {@inherit}
	 */
	public function get($index = NULL, $xss_clean = NULL)
	{
		if($this->mock_state) {
			return $this->_fetch_from_array($this->mock_GET, $index, $xss_clean);
		}

		return parent::get($index, $xss_clean);
	}

	/**
	 * {@inherit}
	 */
	public function post($index = NULL, $xss_clean = NULL)
	{
		if($this->mock_state) {
			return $this->_fetch_from_array($this->mock_POST, $index, $xss_clean);
		}

		return parent::post($index, $xss_clean);
	}

	/**
	 * {@inherit}
	 */
	public function cookie($index = NULL, $xss_clean = NULL)
	{
		if($this->mock_state) {
			return $this->_fetch_from_array($this->mock_COOKIE, $index, $xss_clean);
		}

		return parent::cookie($index, $xss_clean);
	}

	/**
	 * {@inherit}
	 */
	public function server($index, $xss_clean = NULL)
	{
		if($this->mock_state) {
			return $this->_fetch_from_array($this->mock_SERVER, $index, $xss_clean);
		}

		return parent::server($index, $xss_clean);
	}
}