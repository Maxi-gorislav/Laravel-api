<?php

namespace App\Cart;

use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

class Cart
{
    protected $session;
    protected $name;
    
    public function __construct(SessionManager $session)
    {
        $this->session = $session;
        $this->name = 'cart_' . Auth::user()->id;
    }
    
    public function addToCart(Product $product)
    {
        $this->session->push($this->name, $product);
    }
    
    public function removeFromCart(Product $product)
    {
        $current_cart = $this->session->pull($this->name, []);
        if (($key = array_search($product, $current_cart)) !== false) {
            unset($current_cart[$key]);
        }
        $this->session->put($this->name, $current_cart);
    }
    
    public function clearCart()
    {
        $this->session->forget($this->name);
    }
    
    public function getCart()
    {
        return $this->session->get($this->name) ?? [];
    }
}
