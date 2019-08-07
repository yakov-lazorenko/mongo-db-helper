<?php

namespace App\MongoDbHelper;


abstract class BaseModel
{
    /**
     * @var $collection - collection name
     */
    public static $collection;

    public static $relations;

    public static $relations_collections;


    public static function getMongoDbHelper()
    {
        return resolve('MongoDbHelper');
    }


    public static function all($collection = null)
    {
        $mongoDb = static::getMongoDbHelper();
        if ($collection) {
            $documents = $mongoDb->all($collection);
        } else {
            if (!isset(static::$collection)) {
                return false;
            }
            $documents = $mongoDb->all(static::$collection);
        }
        $documents = static::getDocumentsRelations($documents);
        return $documents;
    }


    public static function find($filter = [], $options = [])
    {
        try {
            $mongoDb = static::getMongoDbHelper();
            $cursor = $mongoDb->collection(static::$collection)->find($filter, $options);
            $documents = $mongoDb::getArray($cursor);
            $documents = static::getDocumentsRelations($documents);
        } catch(\Exception $e) {
            return false;
        }
        return $documents;
    }


    public static function findOne($filter = [], $options = [])
    {
        try{
            $mongoDb = static::getMongoDbHelper();
            $document = $mongoDb->collection(static::$collection)->findOne($filter, $options);
            $document['relations'] = self::getRelations($document);
        } catch(\Exception $e) {
            return false;
        }
        return $document;
    }


    public static function findById($id)
    {
        if (!intval($id)) {
            return false;
        }
        try {
            $mongoDb = static::getMongoDbHelper();
            $document = $mongoDb->collection(static::$collection)->findOne(['id'=> (int)$id]);
            $document['relations'] = self::getRelations($document);
        } catch(\Exception $e) {
            return false;
        }
        return $document;
    }


    public static function findByName($name)
    {
        try{
            $mongoDb = static::getMongoDbHelper();
            $document = $mongoDb->collection(static::$collection)->findOne(['name'=> $name]);
            $document['relations'] = self::getRelations($document);
        } catch(\Exception $e) {
            return false;
        }
        return $document;
    }


    public static function getRelations($document)
    {
        if (empty(static::$relations)) {
            return null;
        }
        $relations = [];
        $mongoDb = static::getMongoDbHelper();
        foreach (static::$relations as $relation) {
            $collection = static::getRelationCollection($relation);
            if (!empty($document[$relation])) {
                if (gettype($document[$relation]) == 'array' ||
                    gettype($document[$relation]) == 'object'){
                    $ids = $document[$relation];
                } else {
                    $ids = [$document[$relation]];
                }
                $relations[$relation] =
                    $mongoDb->getDocumentsByIds($collection, $ids);
            } else {
                $relations[$relation] = null;
            }
        }
        return $relations;
    }


    public static function getDocumentsRelations($documents)
    {
        if (empty(static::$relations)) {
            return null;
        }
        $relations_ids = [];
        foreach($documents as $document) {
            foreach (static::$relations as $relation) {
                if (!empty($document[$relation])) {
                    if (gettype($document[$relation]) == 'array') {
                        $ids = $document[$relation];
                    } elseif (gettype($document[$relation]) == 'object') {
                        $ids = iterator_to_array($document[$relation]);
                    } else {
                        $ids = [$document[$relation]];
                    }
                    $ids = array_map('intval', $ids);
                    if ( !empty($relations_ids[$relation]) ) {
                        $relations_ids[$relation] = array_merge(
                            $relations_ids[$relation],
                            $ids
                        );
                    } else {
                        $relations_ids[$relation] = $ids;
                    }
                }
            }
        }

        foreach (static::$relations as $relation) {
            if (!empty($relations_ids[$relation])) {
                $relations_ids[$relation] = array_unique($relations_ids[$relation]);
                sort($relations_ids[$relation]);
            } else {
                $relations_ids[$relation] = null;
            }
        }

        $mongoDb = static::getMongoDbHelper();
        $related_documents = [];
        foreach (static::$relations as $relation) {
            if ($relations_ids[$relation]) {
                $ids = $relations_ids[$relation];
                $collection = static::getRelationCollection($relation);
                $docs = $mongoDb->getDocumentsByIds($collection, $ids);
                if (empty($docs)) {
                    $related_documents[ $relation ] = null;
                    continue;
                }
                foreach ($docs as $doc) {
                    $related_documents[ $relation ] [ $doc['id'] ] = $doc;
                }
            }
        }

        foreach ($documents as &$document) {
            $document_relations = [];
            foreach (static::$relations as $relation) {
                if (!empty($document[$relation])) {
                    if (gettype($document[$relation]) == 'array' ||
                        gettype($document[$relation]) == 'object'
                    ) {
                        $ids = $document[$relation];
                    } else {
                        $ids = [$document[$relation]];
                    }
                    foreach ($ids as $id) {
                        $document_relations[$relation][] =
                            $related_documents[ $relation ] [ $id ];
                    }
                } else {
                    $document_relations[$relation] = null;
                }
            }
            $document['relations'] = !empty($document_relations) ? $document_relations : null;
        }
        return $documents;
    }


    public static function getRelationsByDocumentId($id)
    {
        $document = static::findById($id);
        return static::getRelations($document);
    }


    public static function getRelationCollection($relation)
    {
        if (in_array($relation, array_keys(static::$relations_collections))) {
            $collection = static::$relations_collections[$relation];
        } else {
            $collection = $relation;
        }
        return $collection;
    }

}
