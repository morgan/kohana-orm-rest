# Connections

Setup a [Dispatch](https://github.com/morgan/kohana-dispatch) connection. Models use the "default" 
connection by default which can be changed by setting the `_connection_name` property.

# Creating a model

	class Model_Example extends ORM_REST {}

# Defining Defaults

## Resource path

By default, the URI is built based on the class name. For example, the class `Person_Human` would 
convert to the URI of `person/:person/human`. This particular resource would require the parameter 
`:person` to be specified prior to being used (more on this later in the guide). You can override 
the default URI path by specifying the following property: 

	protected $_resource = 'path/to/resource';
	
## Connection
	
Name of `Dispatch_Connection` to use for this model. If not defined, 
`Dispatch_Connection::instance()` is called.
	
	protected $_connection_name = 'name_of_connection';
	
## Primary key

By default, the primary key is "id". This can be changed by defining the following:

	protected $_primary_key = 'id';	
	
# Parameters

Parameters are used to defined variables within the URI namespace generally in cases of 
parent/child relationships (such as `parent/7/child`). The default convention used when 
generating a class URI is converting a class name of `Parent_Child` to 
`parent/:parent/child/:parent_child`. To set the variable within the URI, use the following:

	// Set :parent
	$orm->param('parent', 7);

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
		->where('id', 1)
		->find();

## Creating

Create an instance of model.

	$person = new Model_Person;

Set values for each of the properties:

	$person->first_name	= 'Micheal';
	$person->last_name	= 'Morgan';
	$person->city		= 'Orlando';
	$person->state		= 'FL';

Then, simply call save:

	$person->save();

This will call `ORM_REST::create` and a new property of `ORM_REST::$id` will be available.

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
