<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ORM REST Result
 * 
 * @package		orm-rest
 * @author		Micheal Morgan <micheal@morgan.ly>
 * @copyright	(c) 2011 Micheal Morgan
 * @license		MIT
 */
class Kohana_ORM_REST_Result implements Iterator, Countable
{
	/**
	 * Rows
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_data = array();
	
	/**
	 * Dispatch Response
	 * 
	 * @access	protected
	 * @var		Dispatch_Response
	 */
	protected $_response;
	
	/**
	 * ORM name
	 * 
	 * @access	protected
	 * @var		ORM_REST
	 */
	protected $_orm_name;
	
	/**
	 * Factory
	 * 
	 * @access	public
	 * @return	ORM_REST_Result
	 */
	public static function factory(Dispatch_Response $response, $orm_name)
	{
		return new ORM_REST_Result($response, $orm_name);
	}
	
	/**
	 * Initialize
	 * 
	 * @access	public
	 * @return	void
	 */
	public function __construct(Dispatch_Response $response, $orm_name)
	{
		$this->_response = $response;

		if (is_string($orm_name))
		{
			$this->_orm_name = $orm_name;
		}
		else
			throw new Kohana_Exception('ORM_REST_Result expecting $orm_name to be a string.');
	}
	
	/**
	 * Whether or not result is loaded
	 * 
	 * @access	public
	 * @return	bool
	 */
	public function loaded()
	{
		return $this->_response->loaded();
	}
	
	/**
	 * Set
	 * 
	 * @access	public
	 * @param	int
	 * @param	ORM_REST
	 * @return	$this
	 */
	public function set(ORM_REST $value)
	{
		if ( ! $value instanceof $this->_orm_name)
			throw new Kohana_Exception('Result originally created for :original class, :provided class provided.', 
				array('original' => $this->_orm_name, 'provided' => get_class($value)));
		
		$this->_data[] = $value;

		return $this;
	}

	/**
	 * Interface: Iterator
	 * 
	 * @access	public
	 * @return	void
	 */
	public function rewind()
	{
		reset($this->_data);
	}
	
	/**
	 * Interface: Iterator
	 * 
	 * @access	public
	 * @return	ORM_REST
	 */
	public function current()
	{
		return current($this->_data);
	}
	
	/**
	 * Interface: Iterator
	 * 
	 * @access	public
	 * @return	int
	 */
	public function key()
	{
		return key($this->_data);
	}
	
	/**
	 * Interface: Iterator
	 * 
	 * @access	public
	 * @return	ORM_REST
	 */
	public function next()
	{
		return next($this->_data);
	}
	
	/**
	 * Interface: Iterator
	 * 
	 * @access	public
	 * @return	bool
	 */
	public function valid()
	{
		$key = key($this->_data);
		
		return ($key !== NULL AND $key !== FALSE);
	}
	
	/**
	 * Interface: Countable
	 * 
	 * @access	public
	 * @return	int
	 */
	public function count()
	{
		return count($this->_data);
	}

	/**
	 * Count total
	 * 
	 * @access	public
	 * @return	int
	 */
    public function count_total()
    {
        return $this->_response['count_total'];
    }
}
