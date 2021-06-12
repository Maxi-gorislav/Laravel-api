<?php

namespace App\Cart;

use Illuminate\Support\Facades\Facade;

class CartFacade extends Facade
{
    protected static function getFacadeAccessor() 
    { 
        return 'App\Cart\Cart';
    }
}
