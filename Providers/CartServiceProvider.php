<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Cart\Cart;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('App\Cart\Cart', function ($app) {
		    return new Cart($app['session']);
	    });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
