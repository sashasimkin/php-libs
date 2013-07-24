<?php
/**
 * Registry
 */


class Reg {

	/**
	 * Data storage
	 *
	 * @var array
	 */
	private static $_store = array();

	/**
	 * Can't create instance
	 */
	private function __construct() {
	}

	/**
	 * Can't clone
	 */
	private function __clone() {
	}

	private function __wakeup() {
	}

	/**
	 * Check if data exists
	 *
	 * @static
	 * @param string $name
	 * @return bool
	 */
	public static function exists( $name ) {
		return isset( self::$_store[$name] );
	}

	/**
	 * Getter by key
	 *
	 * @static
	 * @param $name
	 * @return object
	 */
	public static function get( $name ) {
		return ( isset( self::$_store[$name] ) ) ? self::$_store[$name] : null;
	}

	/**
	 * Set data to store
	 *
	 * @static
	 * @param string $name Ключ для данных.
	 * @param mixed $obj Данные для хранения.
	 * @return mixed
	 */
	public static function set( $name, $obj ) {
		return self::$_store[$name] = $obj;
	}
}
