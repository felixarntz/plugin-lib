<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Options;
use Leaves_And_Love\Plugin_Lib\DB;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Cache;
use WP_UnitTestCase;

class Unit_Test_Case extends WP_UnitTestCase {
	protected static function setUpHooks( $instances ) {
		if ( ! is_array( $instances ) ) {
			$instances = array( $instances );
		}

		foreach ( $instances as $instance ) {
			$instance->add_hooks();
		}

		self::$hooks_saved = array();
	}

	protected static function tearDownHooks( $instances ) {
		if ( ! is_array( $instances ) ) {
			$instances = array( $instances );
		}

		foreach ( $instances as $instance ) {
			$instance->remove_hooks();
		}

		self::$hooks_saved = array();
	}

	protected static function setUpSampleManager( $prefix, $name ) {
		require_once LALPL_TESTS_DATA . 'mvc/sample.php';
		require_once LALPL_TESTS_DATA . 'mvc/sample-collection.php';
		require_once LALPL_TESTS_DATA . 'mvc/sample-query.php';
		require_once LALPL_TESTS_DATA . 'mvc/sample-manager.php';
		require_once LALPL_TESTS_DATA . 'mvc/sample-type.php';
		require_once LALPL_TESTS_DATA . 'mvc/sample-type-manager.php';

		$db = new DB( $prefix, new Options( $prefix ), array(
			'table_already_exist' => 'Table %s already exists.',
			'schema_empty'        => 'Table schema is empty.',
		) );

		$table_name = $name . 's';
		$meta_table_name = $name . 'meta';
		$id_field_name = $name . '_id';

		$max_index_length = 191;
		$db->add_table( $table_name, array(
			"id bigint(20) unsigned NOT NULL auto_increment",
			"type varchar(32) NOT NULL default ''",
			"title text NOT NULL",
			"content longtext NOT NULL",
			"parent_id bigint(20) unsigned NOT NULL default '0'",
			"PRIMARY KEY  (id)",
			"KEY type (type)",
		) );
		$db->add_table( $meta_table_name, array(
			"meta_id bigint(20) unsigned NOT NULL auto_increment",
			"{$prefix}{$id_field_name} bigint(20) unsigned NOT NULL default '0'",
			"meta_key varchar(255) default NULL",
			"meta_value longtext",
			"PRIMARY KEY  (meta_id)",
			"KEY {$prefix}{$id_field_name} ({$prefix}{$id_field_name})",
			"KEY meta_key (meta_key($max_index_length))",
		) );
		$db->set_version( 20161130 );

		$db->check();

		$messages = array(
			'db_insert_error'            => 'Could not insert ' . $name . ' into the database.',
			'db_update_error'            => 'Could not update ' . $name . ' in the database.',
			'meta_delete_error'          => 'Could not delete ' . $name . ' metadata for key %s.',
			'meta_update_error'          => 'Could not update ' . $name . ' metadata for key %s.',
			'db_fetch_error_missing_id'  => 'Could not fetch ' . $name . ' from the database because it is missing an ID.',
			'db_fetch_error'             => 'Could not fetch ' . $name . ' from the database.',
			'db_delete_error_missing_id' => 'Could not delete ' . $name . ' from the database because it is missing an ID.',
			'db_delete_error'            => 'Could not delete ' . $name . ' from the database.',
			'meta_delete_all_error'      => 'Could not delete the ' . $name . ' metadata. The ' . $name . ' itself was deleted successfully though.',
		);

		return new \Leaves_And_Love\Sample_MVC\Sample_Manager( $db, new Cache( $prefix ), $messages, new Meta( $db ), new \Leaves_And_Love\Sample_MVC\Sample_Type_Manager( $prefix ), $name );
	}

	protected static function tearDownSampleManager( $prefix, $name ) {
		global $wpdb;

		$prefixed_table_names = array(
			$prefix . $name . 's',
			$prefix . $name . 'meta',
		);

		foreach ( $prefixed_table_names as $prefixed_table_name ) {
			if ( ! isset( $wpdb->$prefixed_table_name ) ) {
				continue;
			}

			$db_table_name = $wpdb->$prefixed_table_name;
			$wpdb->query( "DROP TABLE $db_table_name" );

			$key = array_search( $prefixed_table_name, $wpdb->tables );
			if ( false !== $key ) {
				$wpdb->tables = array_splice( $wpdb->tables, $key, 1 );
			}

			unset( $wpdb->$prefixed_table_name );
		}

		delete_network_option( null, $prefix . 'db_version' );
	}
}
