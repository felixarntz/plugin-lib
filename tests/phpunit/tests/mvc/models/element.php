<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

/**
 * @group mvc
 * @group models
 * @group elements
 */
class Tests_Element extends Unit_Test_Case {
	protected static $prefix;
	protected static $manager;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$prefix = 'lalpl_tests_element_';

		self::$manager = self::setUpSampleManager( self::$prefix, 'element' );
	}

	public function test_setgetisset_property() {
		$model = self::$manager->create();

		$this->assertSame( 0, $model->id );

		$model->id = 4;
		$this->assertSame( 0, $model->id );

		$this->assertTrue( isset( $model->title ) );

		$title = 'Element Title 1';
		$model->title = $title;
		$this->assertSame( $title, $model->title );
	}

	public function test_setgetisset_meta() {
		$model = self::$manager->create();

		$this->assertFalse( isset( $model->random_value ) );
		$this->assertNull( $model->random_value );

		$value = 'foobar';
		$model->random_value = $value;
		$this->assertTrue( isset( $model->random_value ) );
		$this->assertSame( $value, $model->random_value );

		$model->sync_upstream();
		$this->assertTrue( isset( $model->random_value ) );
		$this->assertSame( $value, $model->random_value );

		$value = 'bar';
		$model->random_value = $value;
		$this->assertTrue( isset( $model->random_value ) );
		$this->assertSame( $value, $model->random_value );

		$model->random_value = null;
		$this->assertFalse( isset( $model->random_value ) );
	}

	public function test_setgetisset_invalid() {
		$model = self::$manager->create();

		$this->assertFalse( isset( $model->pending_properties ) );
		$this->assertNull( $model->pending_properties );
		$model->pending_properties = '';
		$this->assertNull( $model->pending_properties );
	}

	public function test_get_primary_property() {
		$model = self::$manager->create();

		$this->assertSame( 'id', $model->get_primary_property() );
	}

	public function test_get_type_property() {
		$model = self::$manager->create();

		$this->assertSame( 'type', $model->get_type_property() );
	}

	public function test_get_type_object() {
		$model = self::$manager->create();

		self::$manager->register_type( 'foo' );

		$model->type = 'invalid';
		$this->assertNull( $model->get_type_object() );

		$model->type = 'foo';
		$this->assertInstanceOf( 'Leaves_And_Love\Sample_MVC\Sample_Type', $model->get_type_object() );

		self::$manager->unregister_type( 'foo' );
	}

	public function test_sync_upstream() {
		$model = self::$manager->create();

		$model->type = 'foo';
		$model->title = 'Element Title';
		$model->content = rand_long_str( 1500 );
		$model->author_name = 'John Doe';

		$result = $model->sync_upstream();
		$this->assertTrue( $result );

		$this->assertTrue( 0 != $model->id );
	}

	public function test_sync_downstream() {
		$model = self::$manager->create();

		$result = $model->sync_downstream();
		$this->assertWPError( $result );

		$type = 'foo';
		$title = 'Element Title';
		$random = array( 1, 2, 3 );

		$model->type = $type;
		$model->title = $title;
		$model->random = $random;

		$model->sync_upstream();

		$new_type = 'bar';
		$new_title = 'Element Bar Title';
		$new_random = array( 'bar' );

		self::$manager->update( $model->id, array(
			'type'  => $new_type,
			'title' => $new_title,
		) );
		self::$manager->update_meta( $model->id, 'random', $new_random );

		$this->assertSame( $type, $model->type );
		$this->assertSame( $title, $model->title );

		$result = $model->sync_downstream();
		$this->assertTrue( $result );

		$this->assertSame( $new_type, $model->type );
		$this->assertSame( $new_title, $model->title );
		$this->assertSame( $new_random, $model->random );
	}

	public function test_delete() {
		$model = self::$manager->create();

		$result = $model->delete();
		$this->assertWPError( $result );

		$model->sync_upstream();

		$result = $model->delete();
		$this->assertTrue( $result );

		$this->assertSame( 0, $model->id );
	}

	public function test_to_json() {
		$properties = array(
			'type'      => 'foo',
			'title'     => 'Hello',
			'content'   => rand_long_str( 500 ),
			'parent_id' => 3,
		);

		$meta = array(
			'author_name' => 'Bruce Wayne',
			'status'      => 'published',
		);

		$model_id = self::$manager->add( $properties );
		foreach ( $meta as $key => $value ) {
			self::$manager->add_meta( $model_id, $key, $value );
		}

		$model = self::$manager->get( $model_id );

		$expected = array_merge( array( 'id' => $model_id ), $properties, $meta );
		$this->assertEqualSetsWithIndex( $expected, $model->to_json() );
	}
}
