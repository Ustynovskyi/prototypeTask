<?php

namespace App;

class Controller
{


    /**
     * @var \App\Api
     */
    private $Api;

    /**
     * @var \App\View
     */
    private $View;

    /**
     * @var \App\Request
     */
    private $Request;


    public function __construct()
    {
        $this->Api= \App\Api :: getInstance();
        $this->Request = \App\Request 	:: getInstance();

        $this->View = \App\View :: getInstance();
        $this->init();

    }

    private function init()
    {

        if($this->Request->getType()=='get')
            $this->View->parsePage('index');


        $this->View->render();
    }

    private function response($responce)
    {
        $this->View->assign('responce', $responce);
    }






}