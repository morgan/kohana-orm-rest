# Connections

Setup a [Dispatch](https://github.com/morgan/kohana-dispatch) connection. Models use the "default" 
connection by default which can be changed by setting the `_connection_name` property.

# Creating a model

	class Model_Example extends ORM_REST {}

# Interacting with a model

## ORM Instance

	// Use factory pattern
	$member = ORM::factory('member');

	// Simply create new object
	$member = new Model_Member;

## Find all

	$member = ORM::factory('member');

	// Converted into a query string parameter
	$member->where('lastname', 'morgan');

	// Get collection of "members"
	$rows = $member->find_all();

	// Iterate over collection and echo firstname
	foreach ($rows as $row)
	{
		echo $row->firstname;
	}

	// Get total count of collection
	$rows->count_all();

## Find

	// Find member 1
	$member = ORM::factory('member', 1);

	// Or using where
	$member = ORM::factory('member')
		->where('member', 1)
		->find();

## Create

	$member = ORM::factory('member');

	$member->firstname = 'Micheal';
	$member->lastname = 'Morgan';
	$member->save();

	// Record reloaded on save
	echo $member->id;

## Update

	$member = ORM::factory('member', 1);

	$member->city = 'New York';

	$member->save();

## Params

Parameters are URI segments that must be defined prior to a request. Example being a model name 
`Forum_Thread`, which would by convention convert to URI of `forums/:forums_id/threads`. Prior to 
making a request, `$orm->param('forums_id', 1);` would need to be called.

## Whether or not loaded

	if ($member->loaded())
	{
		// it's loaded
	}

## Deleting

	$member = ORM::factory('member', 1);

	$member->delete();

# Relationships

## Has one

Basic setup of relationship using conventions:

	protected $_has_one = array
	(
		'post' => array()
	);

Example overriding conventions:

	protected $_has_one = array
	(
		'post' => array
		(
			'model'			=> 'post',
			'resource'		=> 'forums/:forums_id/posts/:forums_posts_id',
			'foreign_key'	=> array('forums_id' => 1)
		)
	);

## Has many

	protected $_has_many = array
	(
		'post' => array()
	);

## Belongs to

	protected $_belongs_to = array
	(
		'people' => array()
	);

# Resource Conventions

## Example

Model `Forum_Thread_Post` is converted to the following params 
`forums/:forums_id/threads/:forums_threads_id/posts`. Each param must be set; otherwise, an 
exception is thrown. To set a param, simple call `$member->param('forums_id', 1);`.

Purpose for redundant "forums" within "forums_threads_id" is to ensure unique param.

If `ORM_REST::$_resource` is not defined, `ORM_REST::hash` is used to generate the resource using the class name along 
with conventions for generating param keys. If the default conventions do not match the 
remote service, `_resource` property can be defined within the individual model.

For example, if the resource name is "objects" but requires multiple params, it can be 
defined as follows:

	protected $_resource = 'objects/:var1/:var2';
	
	// Params can be defined as follows:
	$this->param('var1', 'example');

The ability to define the resource along with dynamic parameters creates for a great deal 
of flexibility. The default conventions do not always match up with all remote REST services; 
however, defining this property should always allow the model object to map.
