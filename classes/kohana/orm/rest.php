<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ORM REST
 * 
 * Conventions
 * - Model names are to always be singular 
 * 
 * @todo		Reincorporate "initialize" method. Post initialization, treat object 
 * configuration as read only. Purposed for gains on object caching versus delayed initialization.
 * @todo		Implement configuration for naming throughout (such as "id", "_", etc)
 * @todo		Potentially implement "cast_data" concept for handling data before loading
 * @todo		Refactor detection of unset parameters (I.E. "/:")
 * @package		ORM-REST
 * @author		Micheal Morgan <micheal@morgan.ly>
 * @copyright	(c) 2012 Micheal Morgan
 * @license		MIT
 */
class Kohana_ORM_REST extends Model
{
	/**
	 * Convert path to key/value using convention.
	 * 
	 * 	// Response: forums/:forums_id/threads/:forums_threads_id/posts
	 * 	ORM_REST::hash('Forum_Thread_Post');
	 * 
	 * Purpose for redundant "forums" within "forums_threads_id" is to ensure unique param.
	 * 
	 * There is no tailing param given that ORM_REST::find appends ORM_REST::$_primary_id named 
	 * property to the end of the resource.
	 * 
	 * @access	public
	 * @param	string	'Forum_Topic'
	 * @param	string	':'
	 * @param	bool	TRUE
	 * @return	string
	 */
	public static function hash($uri, $var, $pluralize)
	{
		$resource = NULL;
			
		$segments = explode('_', strtolower($uri));
		
		$count = 0;
		
		foreach ($segments as $segment)
		{
			$key = NULL;
			
			for ($i = 0; $i <= $count; $i++)
			{
				$key .= $key ? '_' : $var;
				
				$key .= $pluralize ? Inflector::plural($segments[$i]) : $segments[$i];
			}
			
			$key .= '_id';
			
			if ($resource)
			{
				$resource .= '/';
			}
			
			if ($pluralize)
			{
				$segment = Inflector::plural($segment);
			}
			
			$count++;
			
			if (count($segments) == $count)
			{
				$resource .= $segment;
			}
			else
			{
				$resource .= $segment . '/' . $key;
			}
		}
		
		return $resource;
	}	
	
	/**
	 * Can specify name of Dispatch connection which will be retrieved when initialized.
	 * 
	 * @access	protected
	 * @var		mixed	NULL|string
	 */
	protected $_connection_name;
	
	/**
	 * If not defined, ORM_REST::hash is used to generate the resource using the class name along 
	 * with conventions for generating param keys. If the default conventions don't match the 
	 * remote service, this property can be defined within the individual model.
	 * 
	 * For example, if the resource name is "objects" but requires multiple params, it can be 
	 * defined as follows:
	 * 
	 * 	protected $_resource = 'objects/:var1/:var2';
	 * 
	 * 	// Params can be defined as follows:
	 * 	$this->param('var1', 'example');
	 * 
	 * The ability to define the resource along with dynamic parameters creates for a great deal 
	 * of flexibility. The default conventions do not always match up with all remote REST services; 
	 * however, defining this property should always allow the model object to map.
	 * 
	 * @access	protected
	 * @var		mixed	NULL|string
	 */
	protected $_resource;

	/**
	 * Convert singular model name to plural resource name.
	 * 
	 * @access	protected
	 * @var		bool
	 */
	protected $_pluralize = TRUE;

	/**
	 * Belongs to
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_belongs_to = array();	
	
	/**
	 * Has one
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_has_one = array();
	
	/**
	 * Has many
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_has_many = array();
	
	/**
	 * Position when converting rows into result
	 * 
	 * See `Arr::path` for convention.
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_path_rows = 'rows';
	
	/**
	 * Primary id
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_primary_id = 'id';
	
	/**
	 * Class namespace remove from beginning of resource name
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_class_namespace = 'Model_';
	
	/**
	 * Model name
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_model_name;
	
	/**
	 * Resource name
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_resource_name;
	
	/**
	 * Params
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_params = array();
	
	/**
	 * Query
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_query = array();	
	
	/**
	 * Headers
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_headers = array();
	
	/**
	 * Object data
	 * 
	 * @access	protected
	 * @var		array
	 */
	protected $_object = array();
	
	/**
	 * Related (cache relationships)
	 * 
	 * @access	public
	 * @var		array
	 */
	protected $_related = array();
	
	/**
	 * Whether or not object is loaded
	 * 
	 * @access	protected
	 * @var		bool
	 */
	protected $_loaded = FALSE;
	
	/**
	 * Connection
	 * 
	 * @access	protected
	 * @var		mixed	NULL|Dispatch_Connection
	 */
	protected $_connection;	
	
	/**
	 * Class name
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_class_name;
	
	/**
	 * Method name used during find
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_method_find = Request::GET;	
	
	/**
	 * Method name used during creating
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_method_create = Request::POST;	
	
	/**
	 * Method name used during updating
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_method_update = Request::PUT;
	
	/**
	 * Method name used during deleting
	 * 
	 * @access	protected
	 * @var		string
	 */
	protected $_method_delete = Request::DELETE;	

	/**
	 * Symbol used to identify variable within the resource name when params are being set. 
	 * 
	 * Example:
	 * 
	 * 	// Say resource property is set as follows
	 * 	protected $_resource = 'forums/:forums_id/topics';
	 * 
	 * 	// To set param ":forums_id", call:
	 * 	$this->param('forums_id', 1);

	 * @access	protected
	 * @var		string
	 */
	protected $_var = ':';

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
	
	/**
	 * Initialize
	 * 
	 * @access	public
	 * @return	void
	 */
	public function __construct($id = NULL)
	{
		$this->_class_name = get_class($this);
		
		if ($this->_model_name === NULL)
		{
			$name = strtolower(ltrim(get_class($this), $this->_class_namespace));
			
			$this->_model_name = $name;

			if ($this->_pluralize)
			{
				$name = $this->_inflector($name, '_', TRUE);
			}
			
			$this->_resource_name = $name;
		}
		
		if ($this->_resource === NULL)
		{
			$this->_resource = self::hash($this->_model_name, $this->_var, $this->_pluralize);	
		}		
		
		if ($id)
		{
			$this->set($this->_primary_id, $id);
		}		
	}
	
	/**
	 * Get connection
	 * 
	 * @access	public
	 * @param	mixed	Dispatch_Connection|NULL
	 * @return	mixed	Dispatch_Connection|$this
	 */
	public function connection(Dispatch_Connection $connection = NULL)
	{
		if ($connection === NULL)
		{
			if ($this->_connection === NULL)
			{
				$this->_connection = Dispatch_Connection::instance($this->_connection_name);
			}
			
			return $this->_connection;
		}
			
		$this->_connection = $connection;
		
		return $this;
	}
	
	/**
	 * Get resource path
	 * 
	 * @access	public
	 * @return	string
	 */
	public function resource()
	{
		return $this->_resource;
	}	
	
	/**
	 * Clear
	 * 
	 * @access	public
	 * @return	$this
	 */
	public function clear()
	{
		$this->_object = $this->_params = $this->_related = array();
		
		$this->_resource = $this->_connection = NULL;
		
		$this->_loaded = FALSE;		
		
		return $this;
	}
	
	/**
	 * As array
	 * 
	 * @access	public
	 * @return	array
	 */
	public function as_array()
	{
		return $this->_object;
	}
	
	/**
	 * Whether or not object loaded
	 * 
	 * @access	public
	 * @return	bool
	 */
	public function loaded()
	{
		return $this->_loaded;
	}
	
	/**
	 * Get or set param
	 * 
	 * @access	public
	 * @param	mixed	NULL|string
	 * @return	mixed	array|NULL|$this
	 */
	public function param($key = NULL, $value = NULL)
	{
		if ($key === NULL)
			return $this->_params;
			
		if ($this->_pluralize)
		{
			$segments = explode('_', $key);
			
			$key = array();
			
			foreach ($segments as $segment)
			{
				$key[] = $segment;
			}
			
			$key = implode('_', $key);
		}	
			
		$key = ':' . ltrim($key, ':');	
			
		if ($value === NULL)
		{
			if (isset($this->_params[$key]))
				return $this->_params[$key];
				
			return NULL;
		}
		
		$this->_params[$key] = $value;
		
		return $this;
	}
	
	/**
	 * Add to query string if set or return
	 * 
	 * @access	public
	 * @return	$this
	 */
	public function where($data = NULL, $value = NULL)
	{
		if ($this->_query === NULL)
			return $this->_query;
		
		if ( ! is_array($data))
		{
			$data = array($data => $value);
		}
		
		foreach ($data as $key => $value)
		{
			$this->_query[$key] = $value;
		}
		
		return $this;
	}	
	
	/**
	 * Get or set headers
	 * 
	 * @access	public
	 * @param	mixed	NULL|string
	 * @param	mixed
	 * @return	mixed
	 */
	public function header($key = NULL, $value = NULL)
	{
		if ($key === NULL)
			return $this->_headers;
			
		if (is_array($key))
		{
			$this->_headers = $key;
		}
			
		$this->_headers[$key] = $value;
		
		return $this;
	}
	
	/**
	 * Set data
	 * 
	 * @access	public
	 * @param	mixed	string|array
	 * @param	mixed
	 * @return	$this
	 */
	public function set($key, $value = NULL)
	{
		if (is_array($key))
		{
			$this->_object = Arr::merge($this->_object, $key);
		}
		else
		{
			$this->_object[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Get data
	 * 
	 * @access	public
	 * @param	mixed	NULL|string
	 * @return	mixed
	 */
	public function get($key = NULL)
	{
		if ($key === NULL)
			return $this->_object;
			
		if (isset($this->_object[$key]))
			return $this->_object[$key];
			
		if (isset($this->_related[$key]))	
			return $this->_related[$key];

		if (isset($this->_belongs_to[$key]))
			return $this->_related[$key] = $this->_belongs_to($key);			
			
		if (isset($this->_has_one[$key]))
			return $this->_related[$key] = $this->_has_one($key);
		
		if (isset($this->_has_many[$key]))
			return $this->_related[$key] = $this->_has_many($key);
		
		throw new Kohana_Exception('The :property property does not exist in the :class class.', 
			array(':property' => $key, ':class' => get_class($this)));
	}
	
	/**
	 * Find
	 * 
	 * @access	public
	 * @return	$this
	 */
	public function find()
	{
		if ($this->loaded())
			throw new Kohana_Exception('Object already loaded. To reuse object call "clear()".');

		$uri = $this->_prep_uri();	
			
		if (isset($this->_object[$this->_primary_id]))
		{
			$uri .= '/' . $this->_object[$this->_primary_id];	
		}
		
		$response = $this->connection()->execute($uri, Request::GET, $this->_query, array(), $this->_headers);

		if ($response->loaded())
		{
			$this->set($response->as_array());
			
			$this->_loaded = TRUE;
		}
		else if ($response->code() !== 404)
			throw new Kohana_Exception('Could not successfully make request in :class class with code :code.', array(':class' => get_class($this), ':code' => $response->code()));
		
		return $this;
	}

	/**
	 * Find all
	 * 
	 * @access	public
	 * @return	ORM_REST_Result
	 */
	public function find_all()
	{
		if ($this->_loaded)
			throw new Kohana_Exception('Object already loaded. To reuse object call "clear()".');	

		$response = $this->connection()->execute($this->_prep_uri(), Request::GET, $this->_query, array(), $this->_headers);

		$result = ORM_REST_Result::factory($response, $this->_class_name);		
		
		if ($response->loaded())
		{
			// If "self::$_path_rows" set, attempt to retrieve key from array using dot notation. Otherwise, simply use root.
			$rows = ($this->_path_rows) ? Arr::path($response->as_array(), $this->_path_rows, FALSE) : $response->as_array();
			
			if ($rows)
			{
				foreach ($rows as $row)
				{
					$class = new $this->_class_name;
					
					$class->set($row);
					
					$result->set($class);
				}
			}
			else
				throw new Kohana_Exception('Unable to extract rows. See property "_path_rows".');
		}

		$this->_loaded = TRUE;
		
		return $result;
	}
	
	/**
	 * Save
	 * 
	 * @access	public
	 * @return	$this
	 */
	public function save()
	{
		return (isset($this->_object[$this->_primary_id])) ? $this->update() : $this->create();
	}
	
	/**
	 * Create
	 * 
	 * @todo	Retrieve id. Convention?
	 * @access	public
	 * @return	$this
	 */
	public function create()
	{
		if ($this->_loaded)
			throw new Kohana_Exception('Can not create when object loaded.');

		$response = $this->connection()->execute($this->_prep_uri(), $this->_method_create, $this->_query, $this->_object, $this->_headers);
		
		if ( ! $response->loaded())
			throw Kohana_Exception('Failed creating resource with code :code.', array('code' => $response->code()));
		
		$this->_loaded = TRUE;

		$this->set($response->as_array());
			
		return $this;
	}
	
	/**
	 * Update
	 * 
	 * @access	public
	 * @return	$this
	 */
	public function update()
	{
		if ( ! isset($this->_object[$this->_primary_id]))
			throw new Kohana_Exception('Expecting object to have set id.');

		$response = $this->connection()->execute($this->_prep_uri() . '/' . $this->_object[$this->_primary_id], $this->_method_update, $this->_query, $this->_object, $this->_headers);
	
		if ( ! $response->loaded())
			throw new Kohana_Exception('Failed updating resource with code :code.', array('code' => $response->code()));
		
		$this->_loaded = TRUE;

		$this->set($response->as_array());
			
		return $this;
	}
	
	/**
	 * Delete
	 * 
	 * @access	public
	 * @return	$this
	 */
	public function delete()
	{
		if ( ! isset($this->_object[$this->_primary_id]))
			throw new Kohana_Exception('Expecting object to have set id.');

		$response = $this->connection()->execute($this->_prep_uri() . '/' . $this->_object[$this->_primary_id], $this->_method_delete, $this->_query, $this->_object, $this->_headers);
				
		if ( ! $response->loaded())
			throw new Kohana_Exception('Failed deleting resource with code :code.', array('code' => $response->code()));			
			
		return $this->clear();
	}
	
	/**
	 * Set property
	 * 
	 * @access	public
	 * @param	string
	 * @param	mixed
	 * @return	void
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}	
	
	/**
	 * Get property
	 * 
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}
	
	/**
	 * Handles "belongs to" relationships
	 * 
	 * @access	protected
	 * @param	string
	 * @return	mixed	ORM_REST|FALSE
	 */
	protected function _belongs_to($key)
	{
		return $this->_related($this->_belongs_to[$key] + array('model' => $key));
	}
	
	/**
	 * Handles "has one" relationships
	 * 
	 * @access	protected
	 * @param	string
	 * @return	mixed	ORM_REST|FALSE
	 */
	protected function _has_one($key)
	{
		return $this->_related($this->_has_one[$key] + array('model' => $key));
	}
	
	/**
	 * Handles "has many" relationships
	 * 
	 * @access	protected
	 * @param	string
	 * @return	mixed	ORM_REST|FALSE
	 */
	protected function _has_many($key)
	{
		// Generate model name within the context of the current resource
		// Example: Current resource "Order", called "Products" converts to "Model_Order_Product"
		// Called "products" will be made singular given that convention calls for all models to be 
		// singular
		$config['model'] = $this->_model_name . '_' . Inflector::singular($key);
		
		// Resource path is built appending the called resource to the end
		$config['resource'] = $this->resource() . '/:' . $this->_resource_name . '_id';
		
		// Maps current primary key to remote parameter (using current model name)
		$config['foreign_key'] = array($this->_primary_id => $this->_resource_name . '_id');

		// Create called model
		return $this->_related($this->_has_many[$key] + $config);
	}	
	
	/**
	 * Conventions for relational mapping
	 * 
	 * @access	protected
	 * @param	array
	 * @return	ORM_REST
	 */
	protected function _related(array $config)
	{
		$config += array
		(
			'model'			=> NULL,
			'resource'		=> NULL,
			'foreign_key'	=> array(),
			'parameters'	=> array_keys($this->_params)
		);
		
		$model = self::factory($config['model']);
		
		if ($config['resource'])
		{
			$model->resource($config['resource']);
		}		
		
		// Retrieve from properties
		foreach ($config['foreign_key'] as $key => $name)
		{
			$model->param($name, $this->get($key));
		}			
		
		// Retrieve from parameters
		foreach ($config['parameters'] as $local => $remote)
		{
			// If key not set, use remote
			if (is_int($local))
			{
				$local = $remote;
			}
			
			$model->param($remote, $this->param($local));
		}
		
		return $model;
	}	
	
	/**
	 * Inflector
	 * 
	 * @access	protected
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	protected function _inflector($value, $delimiter, $plural = TRUE)
	{
		$values = explode($delimiter, $value);
		
		$inflected = NULL;
		
		foreach ($values as $value)
		{
			if ($inflected)
			{
				$inflected .= $delimiter;
			}

			$inflected .= $plural ? Inflector::plural(Inflector::singular($value)) : Inflector::singular($value);
		}
		
		return $inflected;
	}
	
	/**
	 * Prep URI by replacing parameters
	 * 
	 * @access	protected
	 * @return	string
	 * @throws	Kohana_Exception
	 */
	protected function _prep_uri()
	{
		if ( ! empty($this->_params))
		{
			$uri = str_replace(array_keys($this->_params), array_values($this->_params), $this->resource());
			
			if ($count_params = $this->_count_params($uri, $this->_params))
				throw new Kohana_Exception('Could not load :class class because :count parameters have not been set.', array(':class' => get_class($this), ':count' => $count_params));		

			return $uri;
		}	
		
		return $this->resource();
	}
	
	/**
	 * Verify parameters have been loaded.
	 * 
	 * @access	protected
	 * @return	mixed	int|bool
	 */
	protected function _count_params($uri, array $params)
	{
		if (strpos($uri, '/:'))
		{
			// Remove parameter values
			$uri = str_replace(array_values($params), '', $uri);

			// Once parameters are removed, check for instances of vars
			if ($count = substr_count($uri, '/:'))
				return $count;
		}
		
		return FALSE;
	}	
}