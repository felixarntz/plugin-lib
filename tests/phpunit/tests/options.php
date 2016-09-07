<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Options;

class Tests_Options extends Unit_Test_Case {
	protected $options;

	public function setUp() {
		parent::setUp();

		$this->options = new Options( $this->prefix );
		$this->options->store_in_network( array( 'db_version', 'great_global_value', 'some_global_value', 'global_deletable', 'another_global_value' ) );
	}

	public function test_get() {
		$version = '1000';
		if ( is_multisite() ) {
			update_network_option( null, $this->prefix . 'db_version', array( get_current_blog_id() => $version ) );
		} else {
			update_option( $this->prefix . 'db_version', $version );
		}
		$result = $this->options->get( 'db_version' );
		$this->assertSame( $version, $result );

		$val = 'some string';
		update_option( $this->prefix . 'custom_db_option', $val );
		$result = $this->options->get( 'custom_db_option' );
		$this->assertSame( $val, $result );

		$default = 3;
		$result = $this->options->get( 'non_existing_option', $default );
		$this->assertSame( $default, $result );

		$result = $this->options->get( 'non_existing_option' );
		$this->assertFalse( $result );
	}

	public function test_add() {
		$result = $this->options->add( 'some_added_option', 33 );
		$this->assertTrue( $result );

		$result = $this->options->add( 'some_added_option', 34 );
		$this->assertFalse( $result );

		$result = get_option( $this->prefix . 'some_added_option' );
		$this->assertEquals( 33, $result );

		$result = $this->options->add( 'great_global_value', 35 );
		$this->assertTrue( $result );

		$result = $this->options->add( 'great_global_value', 36 );
		$this->assertFalse( $result );
	}

	public function test_update() {
		$result = $this->options->update( 'some_local_value', 11 );
		$this->assertTrue( $result );

		$result = $this->options->update( 'some_local_value', 12 );
		$this->assertTrue( $result );

		$result = get_option( $this->prefix . 'some_local_value' );
		$this->assertEquals( 12, $result );

		$result = $this->options->update( 'some_global_value', 13 );
		$this->assertTrue( $result );

		$result = $this->options->update( 'some_global_value', 14 );
		$this->assertTrue( $result );
	}

	public function test_delete() {
		$result = $this->options->delete( 'non_existing_option' );
		$this->assertFalse( $result );

		update_option( $this->prefix . 'existing_option', 'hello' );
		$result = $this->options->delete( 'existing_option' );
		$this->assertTrue( $result );

		$result = get_option( $this->prefix . 'existing_option' );
		$this->assertFalse( $result );

		$result = $this->options->delete( 'global_deletable' );
		$this->assertFalse( $result );

		if ( is_multisite() ) {
			update_network_option( null, $this->prefix . 'global_deletable', array( get_current_blog_id() => 'hello' ) );
		} else {
			update_option( $this->prefix . 'global_deletable', 'hello' );
		}
		$result = $this->options->delete( 'global_deletable' );
		$this->assertTrue( $result );
	}

	public function test_get_for_all_sites() {
		$this->options->update( 'another_local_value', 'haha' );
		$result = $this->options->get_for_all_sites( 'another_local_value' );
		$this->assertEquals( array( get_current_blog_id() => 'haha' ), $result );

		$this->options->update( 'another_global_value', 'hoho' );
		$result = $this->options->get_for_all_sites( 'another_global_value' );
		$this->assertEquals( array( get_current_blog_id() => 'hoho' ), $result );
	}

	public function test_get_networks_with_option() {
		$this->options->update( 'another_local_value', 'hihi' );
		$result = $this->options->get_networks_with_option( 'another_local_value' );
		$this->assertEquals( array( 1 ), $result );

		$result = $this->options->get_networks_with_option( 'not_existing_option' );
		$this->assertEmpty( $result );

		$this->options->update( 'another_global_value', 'hehe' );
		$result = $this->options->get_networks_with_option( 'another_global_value' );
		$this->assertEquals( array( 1 ), $result );

		$result = $this->options->get_networks_with_option( 'global_deletable' );
		$this->assertEmpty( $result );
	}

	public function test_flush() {
		$result = $this->options->flush( 'non_existing_option' );
		$this->assertFalse( $result );

		update_option( $this->prefix . 'existing_option', 'hello' );
		$result = $this->options->flush( 'existing_option' );
		$this->assertTrue( $result );

		$result = $this->options->flush( 'global_deletable' );
		$this->assertFalse( $result );

		if ( is_multisite() ) {
			update_network_option( null, $this->prefix . 'global_deletable', array( get_current_blog_id() => 'hello' ) );
		} else {
			update_option( $this->prefix . 'global_deletable', 'hello' );
		}
		$result = $this->options->flush( 'global_deletable' );
		$this->assertTrue( $result );
	}

	public function test_is_stored_in_network() {
		$result = $this->options->is_stored_in_network( 'some_local_value' );
		$this->assertFalse( $result );

		$result = $this->options->is_stored_in_network( 'some_global_value' );
		$this->assertTrue( $result );
	}

	public function test_store_in_network() {
		$this->options->store_in_network( 'another_custom_global_value' );
		$result = $this->options->is_stored_in_network( 'another_custom_global_value' );
		$this->assertTrue( $result );
	}

	public function test_migrate_to_network() {
		$base_options = array( 'somekey' => 'somevalue' );

		if ( is_multisite() ) {
			$result = $this->options->migrate_to_network( $base_options );
			$result = apply_filters( 'populate_network_meta', $base_options, 2 );
			$this->assertEquals( $base_options, $result );
		} else {
			$new_value = '23';
			delete_option( $this->prefix . 'great_global_value' );
			delete_option( $this->prefix . 'some_global_value' );
			update_option( $this->prefix . 'global_deletable', $new_value );
			delete_option( $this->prefix . 'another_global_value' );
			delete_option( $this->prefix . 'another_custom_global_value' );

			$expected = array_merge( $base_options, array( $this->prefix . 'global_deletable' => array( 1 => $new_value ) ) );
			$result = apply_filters( 'populate_network_meta', $base_options, 1 );
			$this->assertEquals( $expected, $result );
		}
	}
}
