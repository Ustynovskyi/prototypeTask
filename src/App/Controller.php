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


    private function doGetTaskThree()
    {

        $species=$this->Api->getCollection( 'species');

        $list=$species->aggregate([
            ['$unwind'=>'$people'],
            [
                '$lookup' => [
                    'from' => 'people',
                    'localField' => 'people',
                    'foreignField' => 'id',
                    'as' => 'species_people'
                ]
            ],
            ['$unwind'=>'$species_people'],
            [
                '$lookup' => [
                    'from' => 'films',
                    'localField' => 'species_people.id',
                    'foreignField' => 'characters',
                    'as' => 'species_people_films'
                ]
            ],
            ['$unwind'=>'$species_people_films'],
            ['$group'=> [
                '_id' => '$name',
                'filmscount' => [ '$sum' => 1]
            ]],
            ['$sort'=>['filmscount'=>-1]]
        ])->toArray();


        return $this->response(array('line'=>__LINE__, 'code'=>1, 'result'=>$list));
    }




}