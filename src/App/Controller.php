<?php

/**
 * This class is a basic hand-made controller for MVC pattern
 * Utilizes basic Api, View and Request classes
 */

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

    /**
     * This function initializes controller
     */
    private function init()
    {

        /**
         * getting action from HTTP request
         */
        if(!$this->Request->getAction()) $action='index';
        else $action=$this->Request->getAction();

        $funcName='do'.ucfirst($action);

        /**
         * action error handling
         */
        if(!method_exists($this, $funcName)) {
            http_response_code(400);
            die('Method not allowed');
        }

        /**
         * call for requested action
         */
        $this->$funcName();

        /**
         * render of page template
         */
        $this->View->parsePage($this->template);
        $this->View->render();
    }

    /**
     * This function renders basic page template
     */
    private function doIndex()
    {
        $this->template='index';
    }


    /**
     * This function used to json decode and return response for ajax requests
     * View class handles json converting from responce variable
     * @param array $responce
     */
    private function response($responce)
    {
       $this->View->assign('responce', $responce);
       $this->View->sendJSON();
    }


    /**
     * This function creates json formatted response for task four.
     * What planet in Star Wars universe provided largest number of vehicle pilots?
     * return json array
     */
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

    /**
     * This function creates json formatted response for task one.
     * Which of all Star Wars movies has the longest opening crawl (counted by number of characters)?
     * return json array
     */
    private function doGetTaskOne()
    {

        $films = $this->Api->getCollection('films');


        $longestOpening = $films->findOne([], ['sort' => ['openeing-crawl' => -1]]);


        return $this->response(array('line' => __LINE__, 'code' => 1, 'result' => $longestOpening->title));
    }

    /**
     * This function creates json formatted response for task two.
     * What character (person) appeared in most of the Star Wars films?
     * return json array
     */
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

    /**
     * This function creates json formatted response for task three.
     * What species (i.e. characters that belong to certain species) appeared in the most number of Star Wars films?
     * return json array
     */
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