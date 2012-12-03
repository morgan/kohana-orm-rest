# Relationships

## Has one

Setup property in model:

	protected $_has_one = array
	(
		'organization' => array()
	);

Example usage:

	$member = ORM::factory('member', 1);

	echo $member->organization->title;

## Has many

Setup property in model:

	protected $_has_many = array
	(
		'posts' => array
		(
			'model' => 'member_post'
		)
	);

Example usage:

	$member = ORM::factory('member', 1);

	foreach ($member->posts as $post)
	{
		echo $post->title;
	}

## Belongs to

Basic setup of relationship using conventions:

	protected $_belongs_to = array
	(
		'thread' => array()
	);

Example overriding conventions:

	protected $_belongs_to = array
	(
		'thread' => array
		(
			'model'			=> 'thread',
			'resource'		=> 'forums/:forums_id/threads',
			'foreign_key'	=> array('forums_id' => 1)
		)
	);

Example usage:

	$post = ORM::factory('post', 1);

	echo $post->thread->title;
