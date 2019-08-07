<?php

namespace App;


class Shop extends BaseModel
{
    public static $collection = 'shops';

    public static $relations = [
        'currencies', 'owner', 'type', 'countries', 'regions'
    ];

    public static $relations_collections = [
        'payment' => 'payment_methods',
        'countries' => 'geo_countries',
        'regions' => 'geo_regions',
    ];

    public static function getList()
    {
        return parent::all();
    }

    public static function findBySlug($slug)
    {
        return parent::findOne(['slug' => $slug]);
    }
}