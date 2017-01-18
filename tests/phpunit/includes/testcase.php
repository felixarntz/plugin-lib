<?php
/**
 * @package LeavesAndLovePluginLib
 * @subpackage Tests
 */

namespace Leaves_And_Love\Plugin_Lib\Tests;

use Leaves_And_Love\Plugin_Lib\Error_Handler;
use Leaves_And_Love\Plugin_Lib\Options;
use Leaves_And_Love\Plugin_Lib\DB;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_DB;
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
		require_once LALPL_TESTS_DATA . 'db-objects/sample.php';
		require_once LALPL_TESTS_DATA . 'db-objects/sample-collection.php';
		require_once LALPL_TESTS_DATA . 'db-objects/sample-query.php';
		require_once LALPL_TESTS_DATA . 'db-objects/sample-manager.php';
		require_once LALPL_TESTS_DATA . 'db-objects/sample-type.php';
		require_once LALPL_TESTS_DATA . 'db-objects/sample-type-manager.php';
		require_once LALPL_TESTS_DATA . 'db-objects/translations/translations-sample-manager.php';

		$error_handler = new Error_Handler( $prefix, new Translations_Error_Handler() );

		$db = new DB( $prefix, array(
			'options'       => new Options( $prefix ),
			'error_handler' => $error_handler,
		), new Translations_DB() );

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
			"priority float NOT NULL",
			"active boolean NOT NULL",
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

		return new \Leaves_And_Love\Sample_DB_Objects\Sample_Manager( $prefix, array(
			'db'            => $db,
			'cache'         => new Cache( $prefix ),
			'meta'          => new Meta( $prefix, array(
				'db'            => $db,
				'error_handler' => $error_handler,
			) ),
			'type_manager'  => new \Leaves_And_Love\Sample_DB_Objects\Sample_Type_Manager( $prefix ),
			'error_handler' => $error_handler,
		), new \Leaves_And_Love\Sample_DB_Objects\Translations\Translations_Sample_Manager( $name ), $name );
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
				unset( $wpdb->tables[ $key ] );
				$wpdb->tables = array_values( $wpdb->tables );
			}

			unset( $wpdb->$prefixed_table_name );
		}

		delete_network_option( null, $prefix . 'db_version' );
	}

	protected static function setUpCoreManager( $prefix, $type ) {
		$whitelist = array( 'post', 'term', 'comment', 'user' );
		if ( is_multisite() ) {
			$whitelist = array_merge( $whitelist, array( 'site', 'network' ) );
		}

		if ( ! in_array( $type, $whitelist, true ) ) {
			return;
		}

		$error_handler = new Error_Handler( $prefix, new Translations_Error_Handler() );

		$db = new DB( $prefix, array(
			'options'       => new Options( $prefix ),
			'error_handler' => $error_handler,
		), new Translations_DB() );

		$class_name = '';
		$translations = null;
		$services = array(
			'db'            => $db,
			'cache'         => new Cache( $prefix ),
			'meta'          => new Meta( $prefix, array(
				'db'            => $db,
				'error_handler' => $error_handler,
			) ),
			'error_handler' => $error_handler,
		);

		switch ( $type ) {
			case 'post':
				$class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Post_Manager';
				$translations = new \Leaves_And_Love\Plugin_Lib\Translations\Translations_Post_Manager();
				$services['type_manager'] = new \Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Post_Type_Manager( $prefix );
				break;
			case 'term':
				$class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Term_Manager';
				$translations = new \Leaves_And_Love\Plugin_Lib\Translations\Translations_Term_Manager();
				$services['type_manager'] = new \Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Taxonomy_Manager( $prefix );
				break;
			case 'comment':
				$class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Comment_Manager';
				$translations = new \Leaves_And_Love\Plugin_Lib\Translations\Translations_Comment_Manager();
				break;
			case 'user':
				$class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\User_Manager';
				$translations = new \Leaves_And_Love\Plugin_Lib\Translations\Translations_User_Manager();
				break;
			case 'site':
				$class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Site_Manager';
				$translations = new \Leaves_And_Love\Plugin_Lib\Translations\Translations_Site_Manager();
				break;
			case 'network':
				$class_name = 'Leaves_And_Love\Plugin_Lib\DB_Objects\Managers\Network_Manager';
				$translations = new \Leaves_And_Love\Plugin_Lib\Translations\Translations_Network_Manager();
				break;
		}

		return new $class_name( $prefix, $services, $translations );
	}
}
