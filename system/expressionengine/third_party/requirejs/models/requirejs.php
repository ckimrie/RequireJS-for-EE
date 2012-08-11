<?php 


/**
* 
*/
class Requirejs extends CI_Model
{
	
	private static $_scripts 	= array();
	private static $_shims 		= array();



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
		if(!is_array($script)){
			$script = array($script);
		}

		Requirejs::$_scripts[] = array(
			"deps" => $script,
			"callback" => $callback
		);
	}


	static function shim($script='', $deps=array(), $auto_add = TRUE)
	{
		/**
		 * TODO: Validate JS filename
		 */

		if(!is_array($deps)){
			$deps = array($deps);
		}

		Requirejs::$_shims[] = array(
			"script" => $script,
			"deps" => $deps
		);

		Requirejs::load($script);
	}

	static function queue()
	{
		return Requirejs::$_scripts;
	}


	static function shimQueue()
	{
		return Requirejs::$_shims;
	}
}

