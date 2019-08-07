<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\MongoDbHelper\MongoDbHelper;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMongoDb();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function registerMongoDb()
    {
        $mongoDb = new MongoDbHelper([
            'user' => env('MONGO_DB_USERNAME'),
            'password' => env('MONGO_DB_PASSWORD'),
            'database' => env('MONGO_DB_DATABASE'),
            'host' => env('MONGO_DB_HOST'),
            'port' => env('MONGO_DB_PORT'),
        ]);
        $this->app->instance('MongoDbHelper', $mongoDb);
    }

}
