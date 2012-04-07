<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');
/**
 * Tests Dispatch ORM
 *
 * @see			Controller_Dispatch_Test
 * @group		orm-rest
 * @category	Tests
 * @author		Micheal Morgan <micheal@morgan.ly>
 * @copyright	(c) 2011 Micheal Morgan
 * @license		MIT
 */
class Kohana_ORM_RestTest extends Unittest_TestCase
{	
	/**
	 * Verify dependencies
	 * 
	 * @access	protected
	 * @return	void
	 */
	public function setUp()
    {
    	parent::setUp();

    	if ( ! class_exists('Dispatch'))
    	{
    		$this->markTestSkipped('ORM-REST module missing dependency of Dispatch. See documentation.');
    	}
    }	
	
	/**
	 * Data provider for role resources
	 *
	 * @access	public
	 * @return	array
	 */
	public static function provider_hash()
	{
		return array
		(
			array('Forum', 'forums'),
			array('Forum_Thread', 'forums/:forums_id/threads'),
			array('Forum_Thread_Post', 'forums/:forums_id/threads/:forums_threads_id/posts')
		);
	}    
    
    /**
     * Tests class to resource pattern
     * 
	 * @dataProvider	provider_hash
     * @access	public
     * @return	void
     */
    public function test_hash($set, $expected)
    {
    	$this->assertEquals(ORM_REST::hash($set), $expected);
    }
    
	/**
	 * Tests request
	 * 
	 * @access			public
	 * @return			void
	 */	
	public function test_find()
	{
		$orm = $this->_factory()
			->set('id', 1)
			->find();
		
		$this->assertTrue($orm->loaded(), 'Tests model is loaded.');
		
		$this->assertSame($orm->label, 'Test 1');		
	}
	
	/**
	 * Tests find_all
	 * 
	 * @access	public
	 * @return	void
	 */
	public function test_find_all()
	{
		// Sample data REST API is using
		$model = Model::factory('dispatch_test');
		
		// Dispatch ORM
		$orm = $this->_factory();
			
		// Load result
		$result = $orm->find_all();

		// Verify result is loaded
		$this->assertTrue($result->loaded());

		// Get sample data
		$data = $model->get();
		
		// Verify count matches
		$this->assertSame(count($result), count($data), 'Count of sample data should match count of result.');
		
		// Iterator over result
		foreach ($result as $row)
		{
			$this->assertInstanceOf('ORM_REST', $row);
			
			$this->assertTrue((bool) $model->get($row->id), 'Should be able to retrieve same id from sample data.');
		}
	}
	
	/**
	 * Tests create
	 * 
	 * @access	public
	 * @return	void
	 */
	public function test_create()
	{
		$model = Model::factory('dispatch_test');
		
		$data = $model->get();
		
		$next_id = count($data) + 1;
		
		$orm = $this->_factory()
			->save();
			
		$this->assertTrue($orm->loaded());
		
		$this->assertSame($next_id, $orm->id, 'ORM should match model next id.');
	}
	
	/**
	 * Tests update
	 * 
	 * @access	public
	 * @return	void
	 */	
	public function test_update()
	{
		$orm = $this->_factory()
			->set('id', 1)
			->save();
		
		$this->assertTrue($orm->loaded());
	}
	
	/**
	 * Tests update
	 * 
	 * @access	public
	 * @return	void
	 */	
	public function test_delete()
	{
		$orm = $this->_factory()
			->set('id', 1)
			->delete();
		
		$this->assertFalse($orm->loaded());
		
		$this->assertEmpty($orm->as_array(), 'After delete, object should be cleared and data set empty.');
	}	
	
	/**
	 * Factory pattern for ORM Dispatch
	 * 
	 * @access	protected
	 * @return	ORM_Dispatch
	 */
	protected function _factory()
	{
		return Model::factory('orm_rest_test')
			->connection(Dispatch_Connection::factory(Kohana_DispatchTest::config()));
	}
}