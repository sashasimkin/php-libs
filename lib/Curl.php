<?php
/**
 * Curl wrapper
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */


class Curl {
	private $ch;
	private $defaultOpt = array(
		'USERAGENT' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.168 Safari/535.19',
		'RETURNTRANSFER' => true,
		'AUTOREFERER' => true,
		'CONNECTTIMEOUT' => 20,
		'TIMEOUT' => 20
	);
	private $toString = '';

	public function __construct( $url = null, $opt = array(), $exec = false ){
		$this->ch = curl_init( $url );

		$this->setopt_array( $this->defaultOpt );
		$this->setopt_array( $opt );

		if( $exec ) $this->toString = $this->exec();

		if( !$this->toString ) $this->toString = $this->lastError();
	}
	public function setopt( $opt, $val ){
		$opt = strtoupper( $opt );
		return curl_setopt( $this->ch, constant( 'CURLOPT_' . $opt), $val );
	}
	public function setopt_array( $array ){
		$false = array();

		foreach($array as $opt => $val) {
			if(!$this->setopt( $opt, $val )) $false[$opt] = $this->lastError(  );
		}

		return count( $false ) == 0 ? true : $false;
	}
	public function exec( $url = null ){
		if( filter_var( strpos( $url, '://' ) === false ? $url = 'http://' . $url : $url, FILTER_VALIDATE_URL ) ) $this->setopt( 'URL', $url );
		if( strpos( $url, 's://' ) !== false ) {
			$this->setopt( 'SSL_VERIFYPEER', false );
			$this->setopt( 'SSL_VERIFYHOST', false );
		}

		$this->toString = curl_exec( $this->ch );

		return $this->toString;
	}
	public function getInfo(  ){
		return curl_getinfo( $this->ch );
	}
	public function lastError(  ){
		return curl_errno( $this->ch ) == 0 ? false : 'Error: ' . curl_errno( $this->ch ) . ': ' . curl_error( $this->ch );
	}
	public function close(){
		@curl_close( $this->ch );
	}
	public function __toString(){
		return (string) $this->toString;
	}
	public function __destruct(){
		$this->close();
	}

	/**
	 * Get new instance.
	 * It's not singleton, fabric-method
	 *
	 * @param null $url
	 * @param array $opt
	 * @param bool $exec
	 *
	 * @return Curl
	 */
	public static function getInstance( $url = null, $opt = array(), $exec = false ){
		return new Curl( $url, $opt, $exec );
	}
}