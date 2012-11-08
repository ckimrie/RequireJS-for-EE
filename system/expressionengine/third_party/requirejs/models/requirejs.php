<?php


/**
*
*/
class Requirejs extends CI_Model
{

	/**
	 * AMD script queue
	 * @var array
	 */
	private static $_scripts 	= array();

	/**
	 * non-AMD script queue
	 * @var array
	 */
	private static $_shims 		= array();

	/**
	 * JS callback queue
	 * @var array
	 */
	private static $_callbacks  = array();


	function __construct()
	{
		parent::__construct();
	}


	/**
	 * Convenience method for Requirejs::load() that is attached to the EE super object
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $script   Script URL
	 * @param  string      $callback Callback JS
	 */
	public function add($script='', $callback="")
	{
		Requirejs::load($script, $callback);
	}


	/**
	 * Add AMD script to the load queue
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $script   Script URL
	 * @param  string      $callback Callbak JS
	 * @return null
	 */
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


	/**
	 * Add non-AMD script
	 *
	 * This adds a non-AMD script to the queue allong with non-AMD script dependencies
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $script   Script URL
	 * @param  array       $deps     Array of script URL dependencies
	 * @param  boolean     $auto_add
	 * @return null
	 */
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


	/**
	 * Add callback JS to the queue
	 *
	 * Adds JS that is included in the master callback function
	 * that is fired after all scripts have been loaded
	 *
	 * @author Christopher Imrie
	 *
	 * @param  string      $callback JS callback
	 * @return null
	 */
	static function callback($callback = '')
	{
		Requirejs::$_callbacks[] = $callback;
	}


	/**
	 * Get all queued callbacks
	 *
	 * @author Christopher Imrie
	 *
	 * @return array
	 */
	static function callbacks()
	{
		return Requirejs::$_callbacks;
	}


	/**
	 * Get all queued AMD scripts
	 *
	 * @author Christopher Imrie
	 *
	 * @return array
	 */
	static function queue()
	{
		return Requirejs::$_scripts;
	}


	/**
	 * Get all queued non-AMD scripts
	 *
	 * @author Christopher Imrie
	 *
	 * @return array
	 */
	static function shimQueue()
	{
		return Requirejs::$_shims;
	}

}

