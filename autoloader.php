<?php
/**
 * Autoloader file
 *
 * @package LeavesAndLovePluginLib
 * @since 1.0.0
 */

if ( ! class_exists( 'Leaves_And_Love_Autoloader' ) ) :

/**
 * Autoloader class.
 *
 * Contains static methods to load classes.
 *
 * @since 1.0.0
 */
final class Leaves_And_Love_Autoloader {
	/**
	 * Whether the loader has been initialized.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Registered autoloader namespaces.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 * @var array
	 */
	private static $namespaces = array();

	public static function init() {
		if ( self::$initialized ) {
			return true;
		}

		if ( ! function_exists( 'spl_autoload_register' ) ) {
			return false;
		}

		spl_autoload_register( array( __CLASS__, 'load_class' ) );

		self::$initialized = true;

		return true;
	}

	public static function register_namespace( $vendor_name, $project_name, $basedir ) {
		if ( self::namespace_registered( $vendor_name, $project_name ) ) {
			return false;
		}

		if ( ! isset( self::$namespaces[ $vendor_name ] ) ) {
			self::$namespaces[ $vendor_name ] = array();
		}

		self::$namespaces[ $vendor_name ][ $project_name ] = trailingslashit( $basedir );

		return true;
	}

	public static function namespace_registered( $vendor_name, $project_name ) {
		if ( ! isset( self::$namespaces[ $vendor_name ] ) ) {
			return false;
		}

		if ( ! isset( self::$namespaces[ $vendor_name ][ $project_name ] ) ) {
			return false;
		}

		return true;
	}

	public static function unregister_namespace( $vendor_name, $project_name ) {
		if ( ! self::namespace_registered( $vendor_name, $project_name ) ) {
			return false;
		}

		unset( self::$namespaces[ $vendor_name ][ $project_name ] );

		if ( empty( self::$namespaces[ $vendor_name ] ) ) {
			unset( self::$namespaces[ $vendor_name ] );
		}

		return true;
	}

	public static function load_class( $class_name ) {
		$parts = explode( '\\', $class_name );

		$vendor_name = array_shift( $parts );
		if ( ! isset( self::$namespaces[ $vendor_name ] ) ) {
			return false;
		}

		$project_name = array_shift( $parts );
		if ( ! isset( self::$namespaces[ $vendor_name ][ $project_name ] ) ) {
			return false;
		}

		$path = self::$namespaces[ $vendor_name ][ $project_name ] . strtolower( str_replace( '_', '-', implode( '/', $parts ) ) ) . '.php';
		if ( ! file_exists( $path ) ) {
			return false;
		}

		require_once $path;

		return true;
	}
}

endif;
