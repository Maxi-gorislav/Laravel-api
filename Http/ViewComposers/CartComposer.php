<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class CartComposer
{
    public function compose(View $view)
    {
        return $view->with('cart_content', \Cart::getCart());
    }
}
