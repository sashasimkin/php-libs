<?php
/**
 * Curl wrapper
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */


class Curl {
	/**
	 * Curl identifier
	 *
	 * @var resource
	 */
	private $ch;
	/**
	 * Default options to each request
	 *
	 * @var array
	 */
	private $defaultOpt=array('USERAGENT'=>'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.168 Safari/535.19', 'RETURNTRANSFER'=>true, 'AUTOREFERER'=>true, 'CONNECTTIMEOUT'=>20, 'TIMEOUT'=>20);
	/**
	 * Default object string representation
	 *
	 * @var bool|string
	 */
	private $toString='';


	/**
	 * Construct object
	 *
	 * @param null $url
	 * @param array $opt
	 * @param bool $exec
	 */
	public function __construct($url=null, $opt=array(), $exec=false) {
		$this->ch=curl_init($url);

		$this->setopt_array($this->defaultOpt);
		$this->setopt_array($opt);

		if($exec) $this->exec();

		if(!$this->toString) $this->toString=$this->lastError();
	}

	/**
	 * Set option to curl seance
	 *
	 * @param string $opt Option name without `CURLOPT_`
	 * @param mixed $val Option value
	 *
	 * @return bool
	 */
	public function setopt($opt, $val) {
		$opt=strtoupper($opt);

		return curl_setopt($this->ch, constant('CURLOPT_' . $opt), $val);
	}

	/**
	 * Set array with options to current seance
	 * Use same as `$this->setopt`
	 *
	 * @param $array
	 *
	 * @return array|bool
	 */
	public function setopt_array($array) {
		$false=array();

		foreach($array as $opt=>$val) {
			if(!$this->setopt($opt, $val)) $false[$opt]=$this->lastError();
		}

		return count($false) == 0 ? true : $false;
	}

	/**
	 * Execute request on url
	 *
	 * @param null $url
	 *
	 * @return $this Curl
	 */
	public function exec($url=null) {
		if(filter_var(strpos($url, '://') === false ? $url='http://' . $url : $url, FILTER_VALIDATE_URL)) $this->setopt('URL', $url);
		if(strpos($url, 's://') !== false) {
			$this->setopt('SSL_VERIFYPEER', false);
			$this->setopt('SSL_VERIFYHOST', false);
		}

		$this->toString=curl_exec($this->ch);

		return $this;
	}

	/**
	 * Just preg_match wrapper
	 *
	 * @param $pattern
	 * @param $matches
	 * @param int $flags
	 * @param int $offset
	 *
	 * @return $this Curl
	 */
	public function match($pattern, &$matches, $flags=0, $offset=0) {
		preg_match($pattern, $this->toString, $matches, $flags, $offset);

		return $this;
	}

	/**
	 * Just preg_match_all wrapper
	 *
	 * @param $pattern
	 * @param $matches
	 * @param int $flags
	 * @param int $offset
	 *
	 * @return $this Curl
	 */
	public function match_all($pattern, &$matches, $flags=PREG_PATTERN_ORDER, $offset=0) {
		preg_match_all($pattern, $this->toString, $matches, $flags, $offset);

		return $this;
	}

	/**
	 * Get request info
	 *
	 * @param $key
	 *
	 * @return null
	 */
	public function getInfo($key=null) {
		$requestInfo = curl_getinfo($this->ch);
		return $key == null ? $requestInfo : (isset($requestInfo[$key]) ? $requestInfo[$key] : null);
	}

	/**
	 * Get last error if exist
	 *
	 * @return bool|string
	 */
	public function lastError() {
		return curl_errno($this->ch) == 0 ? false : 'Error: ' . curl_errno($this->ch) . ': ' . curl_error($this->ch);
	}

	/**
	 * Close current curl session
	 */
	public function close() {
		@curl_close($this->ch);
	}

	/**
	 * Cast current request contents to string
	 *
	 * @return string
	 */
	public function __toString() {
		return (string) $this->toString;
	}

	/**
	 * Close connection on object destruction
	 */
	public function __destruct() {
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
	public static function getInstance($url=null, $opt=array(), $exec=false) {
		return new Curl($url, $opt, $exec);
	}
}