<?php

/**
 * This class maintains MongoDB connection and handles some of MongoDB intercations
 */

namespace App;


class Api
{

    /**
     * @var \MongoDB\Client
     */
    private $connection;
    protected static $_instance;


    public static function getInstance()
    {
        if (null === self::$_instance) {
            try {
                self::$_instance = new self();
            }
            catch (exception $exception) {

            }
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $this->connection = new \MongoDB\Client('mongodb://candidate:PrototypeRocks123654@ds345028.mlab.com:45028/star-wars');
    }

    /**
     * Function to get collection from MongoDB database
     * @param string $collectionName
     * @return \MongoDB\Collection
     */
    public function getCollection($collectionName)
    {
        return $this->connection->selectCollection('star-wars', $collectionName);
    }
}
