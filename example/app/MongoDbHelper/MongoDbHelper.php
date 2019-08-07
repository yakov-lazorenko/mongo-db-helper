<?php

namespace App\MongoDbHelper;


class MongoDbHelper
{
    public $client;
    public $database;
    public $collection;
    public $user;


    public function __construct($options = null)
    {
        if ($options) {
            $host = !empty($options['host']) ? $options['host'] : 'localhost';
            $port = !empty($options['port']) ? $options['port'] : '27017';
            $config_string = "mongodb://$host:$port";
            if (!empty($options['user']) && !empty($options['password'])) {
                $user = $options['user'];
                $password = $options['password'];
                $config_string = "mongodb://$user:$password@$host:$port";
                $this->user = $user;
            }
            if (!empty($options['database'])) {
                $this->database = $options['database'];
            }
        } else {
            $config_string = "mongodb://localhost:27017";
        }
        $this->client = new \MongoDB\Client($config_string);
    }


    public function client()
    {
        return $this->client;
    }


    public function setDb($database)
    {
        $this->database = $database;
    }


    public function db($database = null)
    {
        if (empty($database)) {
            if ( empty($this->database) ){
                return null;
            }
        } else {
            $this->database = $database;
        }
        return $this->client->{$this->database};
    }


    public function collection($collection)
    {
        if (empty($collection)) {
            if ( empty($this->collection) ) {
                return null;
            }
        } else {
            $this->collection = $collection;
        }
        return $this->db()->{$this->collection};
    }


    /**
     * Return all documents in collection as array.
     *
     * @param $collection - collection name
     * @return array
     */
    public function all($collection)
    {
        try {
            if (empty($collection)) {
                if ( empty($this->collection) ) {
                    return [];
                }
            } else {
                $this->collection = $collection;
            }
            $result = $this->db()->{$this->collection}->find();
            $output = [];
            foreach ($result as $entry) {
                $output[] = $entry;
            }
        } catch (\Exception $e) {
            return false;
        }
        return $output;
    }


    public static function getArrayFromBson($bsonDocument)
    {
       $array = iterator_to_array($bsonDocument);
       $array['_id'] = $bsonDocument['_id']->__toString();
       return $array;
    }


    public static function getArray($cursor)
    {
        $array = [];
        foreach ($cursor as $entry) {
            $array[] = self::getArrayFromBson($entry);
        }
        return $array;
    }


    public function truncate($collection)
    {
        if ($this->collection($collection)->countDocuments()) {
            $this->collection($collection)->drop();
        }
    }


    /**
     * @param $collection
     * @param $ids - array of documents IDs
     * @return array|bool - returns array of BsonDocuments
     */
    public function getDocumentsByIds($collection, $ids)
    {
        foreach ($ids as &$id) {
            $id = (int)$id;
        }
        try {
            $cursor = $this->collection($collection)->find(['id' => ['$in' => $ids]]);
            $documents = $cursor->toArray();
        } catch (\Exception $e) {
            return false;
        }
        return $documents;
    }

}
