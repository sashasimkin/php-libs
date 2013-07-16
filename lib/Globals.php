<?php
/**
 * Get with default from super global arrays
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 *
 * @method static array POST()
 * @method static array GET()
 * @method static array REQUEST()
 * @method static array COOKIE()
 * @method static array SESSION()
 * @method static array SERVER()
 * @method static array ENV()
 * @method static array FILES()
 */
class Globals {
	protected static $defValue = null;

	public static function __callStatic( $name, $args ){
		if( in_array( $name, [
			'POST',
			'GET',
			'REQUEST',
			'COOKIE',
			'SESSION',
			'SERVER',
			'ENV',
			'FILES',
		] ) ){
			$GLOB = $GLOBALS['_' . $name];

			$callback = function($val) use($args) {
				if(isset($args[2]) && is_callable($args[2])) $res = $args[2]($val);
				else $res = htmlspecialchars( $val, ENT_QUOTES );

				return $res;
			};

			if( isset( $args[0] ) && is_scalar( $args[0] ) ) {
				$key = $args[0];

				return isset( $GLOB[$key] ) ? $callback($GLOB[$key]) : ( isset( $args[1] ) && is_scalar( $args[1] ) ? $args[1] : null );
			} else return $GLOB;

		} else return null;
	}
}
