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
