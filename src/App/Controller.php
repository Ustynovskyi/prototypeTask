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

    /**
     * @var String
     */
    private $template;

    public function __construct()
    {
        $this->Api= \App\Api :: getInstance();
        $this->Request = \App\Request 	:: getInstance();

        $this->View = \App\View :: getInstance();
        $this->init();

    }

    private function init()
    {

        if(!$this->Request->getAction()) $action='index';
        else $action=$this->Request->getAction();

        $funcName='do'.ucfirst($action);
        if(!method_exists($this, $funcName)) {
            http_response_code(400);
            die('Method not allowed');
        }

        $this->$funcName();

        $this->View->parsePage($this->template);
        $this->View->render();
    }


    private function doIndex()
    {
        $this->template='index';
    }


    private function response($responce)
    {
        echo(json_encode($responce));
        die();
    }


    private function doGetTaskTwo()
    {
        $films=$this->Api->getCollection( 'films');

        $mostFilms=$films->aggregate([
            ['$unwind'=>'$characters'],
            [ '$group'=> [ '_id' => '$characters' , 'number' => [ '$sum' => 1 ] ] ],
            [ '$sort' => [ 'number' => -1 ] ],
            [ '$limit' => 1 ]
        ]);


        foreach($mostFilms as $film) {$charId=$film->_id;}


        $people=$this->Api->getCollection( 'people');

        $char=$people->findOne(['id'=>$charId]);


        return $this->response(array('line'=>__LINE__, 'code'=>1, 'result'=>$char->name));
    }




}