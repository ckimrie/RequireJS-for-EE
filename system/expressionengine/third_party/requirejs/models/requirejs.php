<?php 


/**
* 
*/
class Requirejs extends CI_Model
{
	
	private static $_scripts = array();



	function __construct()
	{
		parent::__construct();
	}



	public function add($script='', $callback="")
	{
		Requirejs::load($script, $callback);
	}



	static function load($script='', $callback="")
	{
		/**
		 * TODO: Validate JS filename
		 */

		Requirejs::$_scripts[] = array(
			"deps" => $script,
			"callback" => $callback
		);
	}

	static function queue()
	{
		return Requirejs::$_scripts;
	}
}

