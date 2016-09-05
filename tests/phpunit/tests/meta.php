<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Options;
use Leaves_And_Love\Plugin_Lib\DB;
use Leaves_And_Love\Plugin_Lib\Meta;

class Tests_Meta extends Unit_Test_Case {
	protected $element_id;
	protected $meta;

	public function setUp() {
		global $wpdb;

		parent::setUp();

		$db = new DB( $this->prefix, new Options( $this->prefix ), array(
			'table_already_exist' => 'Table %s already exists.',
			'schema_empty'        => 'Table schema is empty.',
		) );

		$max_index_length = 191;
		$db->add_table( 'elementmeta', array(
			"meta_id bigint(20) unsigned NOT NULL auto_increment",
			"{$this->prefix}element_id bigint(20) unsigned NOT NULL default '0'",
			"meta_key varchar(255) default NULL",
			"meta_value longtext",
			"PRIMARY KEY  (meta_id)",
			"KEY {$this->prefix}element_id ({$this->prefix}element_id)",
			"KEY meta_key (meta_key($max_index_length))",
		) );
		$db->set_version( 20160905 );

		$db->check();

		$this->element_id = 1;
		$this->meta = new Meta( $db );

		add_metadata( $this->prefix . 'element', $this->element_id, 'test_key', 'test_value' );
	}

	public function test_add() {
		$result = $this->meta->add( 'element', $this->element_id, 'test_key', 'second_value', true );
		$this->assertFalse( $result );

		$result = $this->meta->add( 'element', $this->element_id, 'test_key', 'second_value' );
		$this->assertInternalType( 'int', $result );

		$result = $this->meta->add( 'randtype', $this->element_id, 'test_key', 'second_value' );
		$this->assertFalse( $result );
	}

	public function test_update() {
		$result = $this->meta->update( 'element', $this->element_id, 'test_key2', 'test_value2' );
		$this->assertInternalType( 'int', $result );

		$result = $this->meta->update( 'element', $this->element_id, 'test_key', 'new_value' );
		$this->assertTrue( $result );

		$result = $this->meta->update( 'element', $this->element_id, 'test_key', 'newer_value', 'invalid_value' );
		$this->assertFalse( $result );
	}

	public function test_delete() {
		$result = $this->meta->delete( 'element', $this->element_id, 'delete_key' );
		$this->assertFalse( $result );

		add_metadata( $this->prefix . 'element', $this->element_id, 'delete_key', 'value' );
		$result = $this->meta->delete( 'element', $this->element_id, 'delete_key' );
		$this->assertTrue( $result );
	}

	public function test_get() {
		$result = $this->meta->get( 'element', $this->element_id, 'invalid_key' );
		$this->assertEmpty( $result );

		$result = $this->meta->get( 'element', $this->element_id, 'invalid_key', true );
		$this->assertFalse( $result );

		update_metadata( $this->prefix . 'element', $this->element_id, 'test_key', 'test_value' );
		$result = $this->meta->get( 'element', $this->element_id, 'test_key', true );
		$this->assertSame( 'test_value', $result );
	}

	public function test_exists() {
		$result = $this->meta->exists( 'element', $this->element_id, 'invalid_key' );
		$this->assertFalse( $result );

		$result = $this->meta->exists( 'element', $this->element_id, 'test_key' );
		$this->assertTrue( $result );
	}

	public function test_delete_all() {
		$id = 34;

		$this->meta->update( 'element', $id, 'key1', 'value1' );
		$this->meta->update( 'element', $id, 'key2', 'value2' );
		$this->meta->update( 'element', $id, 'key3', 'value3' );
		$this->meta->update( 'element', $id, 'key4', 'value4' );

		$result = $this->meta->delete_all( 'element', $id );
		$this->assertTrue( $result );

		$result = $this->meta->get( 'element', $id );
		$this->assertEmpty( $result );
	}
}
