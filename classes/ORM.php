<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ORM REST
 * 
 * @package		ORM-REST
 * @author		Micheal Morgan <micheal@morgan.ly>
 * @copyright	(c) 2012 Micheal Morgan
 * @license		MIT
 */
if (class_exists('Kohana_ORM'))
{
	class ORM extends Kohana_ORM {}
}
else
{
	class ORM
	{
		/**
		 * Factory pattern
		 * 
		 * @access	public
		 * @return	ORM_REST
		 */
		public static function factory($class, $id = NULL)
		{
			$class = 'Model_' . $class;
			
			return new $class($id);
		}
	}
}
