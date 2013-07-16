<?php
/**
 * Classes loader
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */

class Autoload {
	/**
	 * Container with all registered autoload functions
	 *
	 * @var array
	 */
	private static $autoloadFunctions;

	/**
	 *  Register new path for search classes
	 *
	 * @param $path
	 * @param bool $silent
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function registerPath($path, $silent=true) {
		if(is_dir($path)) {
			self::$autoloadFunctions[$path]=function ($className) use ($path, $silent) {
				$file=$path . DIRECTORY_SEPARATOR . $className . '.php';

				if(is_file($file)) {
					return (bool) include $file;
				} else {
					if(!$silent) {
						throw new Exception('Can\'t load file ' . $file);
					} else return false;
				}
			};

			return spl_autoload_register(self::$autoloadFunctions[$path]);
		} else {
			if(!$silent) {
				throw new Exception('Path does not exists.');
			} else return false;
		}
	}

	/**
	 * Unregister previously registered path for autoloading
	 *
	 * @param $path
	 * @param bool $silent
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function unregisterPath($path, $silent=true) {
		if(isset(self::$autoloadFunctions[$path])) {
			unset(self::$autoloadFunctions[$path]);

			return spl_autoload_unregister(self::$autoloadFunctions[$path]);
		} else {
			if(!$silent) {
				throw new Exception('Path never been registered.');
			} else return false;
		}
	}

	/**
	 * Getter for autoload functions
	 *
	 * @return array
	 */
	public static function autoloadFunctions() {
		return self::$autoloadFunctions;
	}
}