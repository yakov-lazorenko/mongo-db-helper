<?php

namespace App\Http\Controllers;

use App\MongoDbHelper;
use App\Shop;
use App\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Shops list
     *
     * @param Request $request
     * @return View
     */
    public function list(Request $request)
    {
        $shops = Shop::all();
        $language_code = app()->getLocale();
        $page = Page::findShopsList($language_code);
        return view('shops.list', compact('shops', 'page'));
    }

    /**
     * Shop review page
     *
     * @param integer $slug - shop slug
     * @return View
     */
    public function review($slug)
    {
        $shop = Shop::findBySlug($slug);
        $language_code = app()->getLocale();
        $page = Page::findShopReview($language_code, $slug);
        return view('shops.review', compact('shop', 'page'));
    }

}

