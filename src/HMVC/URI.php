<?php

class HMVC_URI extends CI_URI
{
	/**
	 * Support publicly set uri string
	 *
	 * @param string $str
	 */
	public function set_uri_string($str)
	{
		return $this->_set_uri_string($str);
	}
}