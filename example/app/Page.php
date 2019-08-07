<?php

namespace App;

class Page extends BaseModel
{
    public static $collection = 'pages';

    public static function getList()
    {
        return parent::all();
    }

    public static function getShopReviewPage($language_code, $shop_slug)
    {
        return parent::findOne([
            'language_code' => $language_code,
            'type' => 'shop_review',
            'param' => $shop_slug,
        ]);
    }
}