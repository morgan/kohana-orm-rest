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