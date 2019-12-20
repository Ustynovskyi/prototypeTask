<?php

namespace App;


class Request
{
    private static $_instance;

    private $_SERVER;
    private $_POST;


    public function __construct()
    {
        $this->_SERVER =& $_SERVER;
        $this->_POST =& $_POST;

    }

    public function getUri() {

        return $this->_SERVER['REQUEST_URI'];

    }

    public function getType()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }


    public static function getInstance()
    {
        if (null === self :: $_instance) {
            self :: $_instance = new self();
        }
        return self :: $_instance;
    }

    public function getAction()
    {
        if($this->getUri()=='' || $this->getUri()=='/') return false;
        return (explode('/', $this->getUri()))[1];

    }

}