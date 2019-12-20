<?php

/**
 * This class is used for handling XHTTP requests
 */

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

    /**
     * @return string
     */
    public function getUri() {

        return $this->_SERVER['REQUEST_URI'];

    }

    /**
     * returns request types like POST or GET
     * @return string
     */
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

    /**
     * Get action from XHTTP request specified as first string in GET request after '/' symbol
     * Request format as following "/<action anme>"
     * @return string
     */
    public function getAction()
    {
        if($this->getUri()=='' || $this->getUri()=='/') return false;
        return (explode('/', $this->getUri()))[1];

    }

}