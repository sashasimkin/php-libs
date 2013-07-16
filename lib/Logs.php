<?php
/**
 * Logs reader/writer
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */

class Logs {
	/**
	 * Хранение объектов для модов.
	 *
	 * @var array
	 */
	private static $mods = array();

	/**
	 * Путь к папке логов, в которой будут создаваться папки с логами.
	 *
	 * @var bool
	 */
	private static $patch = false;

	/**
	 * Передача пути к папке с логами, метод дожен быть обязтельно вызван перед self::getInstance();
	 *
	 * @static
	 * @param string $patch Путь к папке.
	 * @throws Exception
	 */
	public static function setPatch( $patch ) {
		if( is_dir( $patch ) ) {
			self::$patch = DIRECTORY_SEPARATOR != '/' ? str_replace( '/', DIRECTORY_SEPARATOR, $patch ) : $patch;
		}
		else throw new Exception( 'Logs patch invalid.' );
	}

	/**
	 * Получения объекта логов.
	 *
	 * @static
	 * @param $mod Папка мода
	 * @return object
	 */
	public static function getInstance( $mod ) {
		$class = __CLASS__;
		return isset( self::$mods[$mod] ) ? self::$mods[$mod] : self::$mods[$mod] = new $class( $mod );
	}

	/**
	 * Папка с логами для отдельно взятого объекта.
	 *
	 * @var string
	 */
	private $dir = '';

	/**
	 * Передача пути в $this->dir;
	 *
	 * @param $mod string Название мода( папки )
	 * @throws Exception
	 */
	public function __construct( $mod ) {
		if( !self::$patch ) throw new Exception( 'Logs patch invalid.Mod name: ' . $mod );
		$this->dir = self::$patch . $mod . DIRECTORY_SEPARATOR;

		$mkRes = false;
		$checkRes = is_dir( $this->dir );

		if( !$checkRes ) $mkRes = @mkdir( $this->dir, 0755 );

		if( !$mkRes && !$checkRes ) throw new Exception( 'Permission denied.Can \'t create dir ' . $this->dir );
	}

	/**
	 * Получения содержимого лога.
	 *
	 * @param $name Название лога.
	 * @param $unserialize Возвращать ли чистый массив? Если false или не указан, то будет возвращаться массив в котором каждая строка сериазиованна.
	 * @return array|bool Массив-содержимое лога в случае успеха или false в случае неудачи.
	 */
	public function get( $name, $unserialize = false ) {
		if( $this->exists( $name ) ) {
			$log = file( $this->dir . $name . '.txt' );
			if( !$unserialize ) {
				return $log;
			}
			else {
				$uLog = array();
				foreach( $log as $line ) {
					$uLog[] = unserialize( $line );
				}
				return $uLog;
			}
		}
		else return false;
	}

	/**
	 * Создаёт новый лог-файл.
	 *
	 * @param $name Название лога.
	 * @return bool Создан ли лог?
	 */
	public function create( $name ) {
		if( !$this->exists( $name . '.txt' ) ) {
			$fp = fopen( $this->dir . $name . '.txt', "w" );
			fclose( $fp );
			return true;
		}
		else return false;
	}

	/**
	 * Удаление лога.
	 *
	 * @param $name Название лога.
	 * @return bool Удалён ли лог?
	 */
	public function delete( $name ) {
		if( $this->exists( $name ) ) {
			return unlink( $this->dir . $name . '.txt' );
		}
		else return false;
	}

	/**
	 * Запись в лог.
	 *
	 * @param $name Лог-файл в который писать.
	 * @param $data Данные которые писать в лог, обязательно должно быть массивом.
	 * @param bool $systemParams Записывать ли системные параметры, если true - будет записано время, IP, REFERER
	 *
	 * @return void
	 */
	public function write( $name, $data, $systemParams = true ) {
		if( !$this->exists( $name ) ) $this->create( $name );

		if( $systemParams ) {
			$systemData = array();

			$systemData['time'] = $this->getDate( 2, 'd M Y H:m' );
			$systemData['ip'] = $_SERVER['REMOTE_ADDR'];
			$systemData['referer'] = empty( $_SERVER['HTTP_REFERER'] ) ? 'null' : $_SERVER['HTTP_REFERER'];

			$data = array_merge( $systemData, $data );
		}

		$data = serialize( $data );

		$f = fopen( $this->dir . $name . '.txt', 'a' );
		flock( $f, LOCK_EX );
		fwrite( $f, $data . "\r\n" );
		fclose( $f );
	}

	/**
	 * Проверка существования лога.
	 *
	 * @param $name Имя лога.
	 * @return bool Существует или нет.
	 */
	private function exists( $name ) {
		return is_file( $this->dir . $name . '.txt' );
	}

	/**
	 * Получение удобного формата даты для записи в логи.
	 *
	 * @param int $type Тип месяцев, влияет только на окончание в месяцах.
	 * @param string $format Формат даты, аналогичен форматированию date();
	 * @param bool $unixTime Если указано - будет использоваться из веремни которое указано в переменной, иначе будет текущее время, тоесть time();
	 * @return string Отформатированную дату.
	 */
	private function getDate( $type = 1, $format = 'G M Y', $unixTime = false ) {
		if( !$unixTime ) $unixTime = time();

		$months = array(
			1 => array( 'Нулябрь', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь' ),
			2 => array( 'Нулября', 'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря' ),
			3 => array( 'Нулябре', 'Январе', 'Феврале', 'Марте', 'Апреле', 'Мае', 'Июне', 'Июле', 'Августе', 'Сентябре', 'Октябре', 'Ноябре', 'Декабре' )
		);

		$currMonth = (int)date( 'm', $unixTime );
		$monthCodes = array( date( 'F', $unixTime ) => $months[$type][$currMonth], date( 'M', $unixTime ) => $months[$type][$currMonth] );

		return strtr( date( $format, $unixTime ), $monthCodes );
	}
}