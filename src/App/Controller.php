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


    private function doGetTaskFour()
    {


        $planets = $this->Api->getCollection( 'planets');

        $list=$planets->aggregate([
            ['$lookup'=>[
                'from' => 'people',
                'localField' => 'id',
                'foreignField' =>'homeworld',

                'as' => 'planet_characters'
            ]
            ],
            ['$unwind'=>'$planet_characters'],
            [
                '$lookup'=>[
                    'from' => 'starships',
                    'let' => ['character_id' => '$planet_characters.id'],
                    'pipeline' => [
                        ['$match' => ['$expr'=>['$in'=>['$$character_id', '$pilots']]]]
                    ],

                    'as' => 'character_starships'
                ]
            ],
            [
                '$lookup'=>[
                    'from' => 'vehicles',
                    'let' => ['character_id' => '$planet_characters.id'],
                    'pipeline' => [
                        ['$match' => ['$expr'=>['$in'=>['$$character_id', '$pilots']]]]
                    ],

                    'as' => 'character_vehicles'
                ]
            ],
            ['$lookup'=>[
                'from' => 'species',
                'let' => ['character_id' => '$planet_characters.id'],
                'pipeline' => [
                    ['$match' => ['$expr'=>['$in'=>['$$character_id', '$people']]]]
                ],
                'as' => 'planet_characters.character_species'
            ]
            ],

            ['$match' => [
                '$expr' => [
                    '$or'=>[
                        ['$gt' => [['$size' => ['$character_starships']], 0]],
                        ['$gt' => [['$size' => ['$character_vehicles']], 0]]
                    ]
                ]
            ]],
            [
                '$group' => [
                    '_id' => [
                        '_id'=>'$_id',
                        'name' => '$_name'
                    ],
                    'pilots' =>[
                        '$push' => [
                            "name" => '$planet_characters.name',
                            'species' =>  [ '$arrayElemAt' => ['$planet_characters.character_species',0] ]
                        ]
                    ],
                    'pilotscount' => [ '$sum' => 1]
                ]
            ],
            ['$sort'=>['pilotscount'=>-1]]


        ])->toArray();
        foreach($list as $item) {
            $item->pilots_formated=[];
            foreach($item->pilots  as $pilot)  $item->pilots_formated[]=$pilot->name.' - '.(isset($pilot->species) ? $pilot->species->name: 'unknown');
        }


        return $this->response(array('line'=>__LINE__, 'code'=>1, 'result'=>$list));
    }

    private function doGetTaskOne()
    {

        $films = $this->Api->getCollection('films');


        $longestOpening = $films->findOne([], ['sort' => ['openeing-crawl' => -1]]);


        return $this->response(array('line' => __LINE__, 'code' => 1, 'result' => $longestOpening->title));
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