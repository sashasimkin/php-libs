<?php
/**
 * Wrap array with useful methods
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */

class ArrayWrapper {
    private $_store;

    public function __construct($haystack){
        $this->_store = is_array($haystack) ? $haystack : array();
    }

    public function __get($name) {
        return isset($this->_store[$name]) ? $this->_store[$name] : null;
    }

    public function __set($name, $value) {
        return $this->_store[$name] = $value;
    }
}