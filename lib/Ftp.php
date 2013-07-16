<?php
/**
 * FTP wrapper
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */

class FTP {

	/**
	 * Идентификатор соединения с FTP-сервером.
	 *
	 * @var resource
	 */
	private $connId;

	/**
	 * Конструктор класса.
	 *
	 * @param string $server FTP-сервер.
	 * @param string $user Имя пользователя.
	 * @param string $pass Пароль юзера.
	 */

	function __construct( $server, $user, $pass ) {
		$this->connect( $server, $user, $pass );
	}

	/**
	 * Подключение к серверу и запись идентификатора
	 *
	 * @param string $server FTP-сервер.
	 * @param string $user Имя пользователя.
	 * @param string $pass Пароль юзера.
	 * @param boolean $ssl SSL-коннект?
	 * @return resource Идентификатор соединения с базой данных.
	 */
	function connect( $server, $user, $pass, $ssl = false ) {
		if( $ssl ) {
			$this->connId = ftp_ssl_connect( $server );
		}
		else $this->connId = ftp_connect( $server );

		if( $this->connId ) {
			if( !ftp_login( $this->connId, $user, $pass ) ) {
				return false;
			}
			else return $this->connId;
		}
		else {
			return false;
		}
	}

	/**
	 * Пассивное или активное соединение будет использовано при передаче.
	 *
	 * @param boolean $bool Пассивное или активное соединени.
	 * @return boolean Получилось ли установить пассивный тип передачи данных.
	 */
	function isPassive( $bool ) {
		return ftp_pasv( $this->connId, $bool );
	}

	/**
	 * Выполнение поизвольной команды на FTP-сервере.
	 *
	 * @param string $command Комманда для сервера.
	 * @return boolean Выопнилась ли команда.
	 */
	function exec( $command ) {
		return ftp_exec( $this->connId, $command );
	}

	/**
	 * Получение текущей директории, в которой находится скрипт.
	 *
	 * @return string Текущая директория.
	 */
	function pwd() {
		return ftp_pwd( $this->connId );
	}

	/**
	 * Переход в корневой каталог FTP-сервера.
	 *
	 * @return boolean Получилось-ли перейти в корневой каталог.
	 */
	function cdup() {
		return ftp_cdup( $this->connId );
	}

	/**
	 * Смена директории.
	 *
	 * @param string $dir Название директории.
	 * @return boolean Удалось-ли сменить директорию.
	 */
	function chdir( $dir ) {
		if( !ftp_chdir( $this->connId, $dir ) ) {
			return false;
		}
		else return $this->pwd();
	}

	/**
	 * Создание директории на FTP-сервере.
	 *
	 * @param string $dir Название новой директории.
	 * @return boolean Удалось-ли создать директорию.
	 */
	function mkdir( $dir ) {
		return ftp_mkdir( $this->connId, $dir );
	}

	/**
	 * Удаление директории на сервере.
	 *
	 * @param string $dir
	 * @return boolean Удалось-ли создать директорию.
	 */
	function rmdir( $dir ) {
		return ftp_rmdir( $this->connId, $dir );
	}

	/**
	 * Удаление файла на FTP-сервере.
	 *
	 * @param string $file Имя удалённого файла.
	 * @return boolen Удалось-ли удалить файл.
	 */
	function delete( $file ) {
		return ftp_delete( $this->connId, $file );
	}

	/**
	 * Изменение прав файлу или дирректории на FTP-сервере.
	 *
	 * @param string $file Сам файл.
	 * @param integer $perms Новые права на файл.
	 * @return boolean Удалось-ли сменить права.
	 */
	function chmod( $file, $perms ) {
		return ftp_chmod( $this->connId, $perms, $file );
	}

	function rawlist( $dir ) {
		$buff = ftp_rawlist( $this->connId, $dir );

		if( !$buff ) return false;

		foreach( $buff as $line ) {
			$line = trim( preg_replace( "/\s+/", " ", $line ) );

			preg_match( '/(d|l|s]p|-)(.*) ([0-9]{1,50}) ([A-Za-z]{3} \d{1,2} \d{2}:\d{2}) (.*)/', $line, $out );
			$out = array_slice( $out, 1 );
			$res['type'] = $out[0];
			$res['size'] = $this->formateSize( $out[2] );
			$res['date'] = $this->myDate( 2, 'd M Y H:m', strtotime( $out[3] ) );
			$res['bitesSize'] = $out[2];

			if( $res['type'] == 'd' ) {
				$fileList['d'][$out[4]] = $res;
			}
			elseif( $res['type'] == '-' ) $fileList['f'][$out[4]] = $res;

			unset( $out, $res );

		}

		ksort( $fileList );

		return $fileList;
	}

	function raw( $command ) {
		return ftp_raw( $this->connId, $command );
	}

	function fileTime( $file, $format = 'd M Y H:m' ) {
		$res = ftp_mdtm( $this->connId, $file );
		if( $res != -1 ) {
			return $this->myDate( 2, $format, $res );
		}
		else return false;
	}

	function size( $file ) {
		$res = ftp_size( $this->connId, $file );
		if( $res != -1 ) {
			return $this->formateSize( $res );
		}
		else return false;
	}

	function rename( $oldname, $newname ) {
		return ftp_rename( $this->connId, $oldname, $newname );
	}

	function getOptions() {
		return ftp_get_option( $this->connId, $option );
	}

	function setOptions( $option ) {
		return ftp_set_option( $this->connId, $option );
	}

	function alloc( $localFile, $result = false ) {
		return ftp_alloc( $this->connId, filesize( $localFile ), $result );
	}

	function put( $localFile, $remoteFile, $mode = FTP_BINARY ) {
		return ftp_put( $this->connId, $remoteFile, $localFile, $mode );
	}

	function get( $remoteFile, $localFile, $mode = FTP_BINARY ) {
		return ftp_get( $this->connId, $localFile, $remoteFile, $mode );
	}

	function fput( $fp, $remoteFile, $mode = FTP_ASCII ) {
		return ftp_fput( $this->connId, $remoteFile, $fp, $mode );
	}

	function fget( $remoteFile, $handle, $mode = FTP_ASCII ) {
		return ftp_fget( $this->connId, $handle, $remoteFile, $mode, 1 );
	}

	function nb_continue() {
		return ftp_nb_continue( $this->connId );
	}

	function nb_put( $localFile, $remoteFile, $callback, $mode = FTP_BINARY ) {
		$res = ftp_nb_put( $this->connId, $remoteFile, $localFile, $mode );

		while( $res == FTP_MOREDATA ) {

			$callback( $i );

			$res = $this->nb_continue();
		}

		if( $res != FTP_FINISHED ) {
			return false;
		}
		else return true;
	}

	function nb_get( $remoteFile, $localFile, $callback, $mode = FTP_BINARY ) {
		$res = ftp_nb_get( $this->connId, $localFile, $remoteFile, $mode );

		while( $res == FTP_MOREDATA ) {

			$callback( $i );

			$res = $this->nb_continue();
		}

		if( $res != FTP_FINISHED ) {
			return false;
		}
		else return true;
	}

	function nb_fput( $remoteFile, $fp, $callback, $mode = FTP_BINARY ) {
		$res = ftp_nb_fput( $this->connId, $remoteFile, $fp, $mode );

		while( $res == FTP_MOREDATA ) {

			$callback( $i );

			$res = $this->nb_continue();
		}
		if( $res != FTP_FINISHED ) {
			return false;
		}
		else return true;
	}

	function nb_fget( $remoteFile, $handle, $callback, $mode = FTP_BINARY ) {
		$res = ftp_nb_fget( $this->connId, $handle, $remoteFile, $mode );

		while( $res == FTP_MOREDATA ) {

			$callback( $i );

			$res = $this->nb_continue();
		}
		if( $res != FTP_FINISHED ) {
			return false;
		}
		else return true;
	}

	function close() {
		return ftp_close( $this->connId );
	}

	function formateSize( $size ) {
		if( $size >= 1073741824 ) {
			$size = round( $size / 1073741824 * 100 ) / 100 . " Gb";
		}
		elseif( $size >= 1048576 ) {
			$size = round( $size / 1048576 * 100 ) / 100 . " Mb";
		}
		elseif( $size >= 1024 ) {
			$size = round( $size / 1024 * 100 ) / 100 . " Kb";
		}
		else {
			$size = $size . " b";
		}
		return $size;
	}

	function myDate( $type = 1, $format = 'd M Y', $unixTime = FALSE ) {
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

?>
