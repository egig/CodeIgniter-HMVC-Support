<?php

class HMVC_Migration extends CI_Migration {

	protected $_migration_type = 'timestamp';

	/**
	 * Retrieves list of available migration scripts
	 *
	 * @return	array	list of migration file paths sorted by version
	 */
	public function find_migrations()
	{

		$migrations = array();

		foreach (Modules::get_installed() as $name => $path) {

			$migration_path = $path.'/migrations/';


			// Load all *_*.php files in the migrations path
			foreach (glob($migration_path.'*_*.php') as $file)
			{
				$name = basename($file, '.php');

				// Filter out non-migration files
				if (preg_match($this->_migration_regex, $name))
				{
					$number = $this->_get_migration_number($name);

					// There cannot be duplicate migration numbers
					if (isset($migrations[$number]))
					{
						$this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $number);
						show_error($this->_error_string);
					}

					$migrations[$number] = $file;
				}
			}
		}

		ksort($migrations);
		return $migrations;
	}

	/**
	 * Get current version
	 * CI migration doesn't have public method
	 */
	public function get_version()
	{
		return $this->_get_version();
	}
}