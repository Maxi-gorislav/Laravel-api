<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $product = Product::find($request->product_id);
        if ($product && $product->available) {
            $current_cart = \Cart::getCart();
            if (!in_array($product, $current_cart)) {
                \Cart::addToCart($product);
                $response['status'] = 'added';
                $response['trans'] = __('cart.added');
            } else {
                $response = [
                    'status' => 'in_cart_already',
                    'trans' => __('cart.in_cart_already')
                ];
            }
        } else {
            $response = [
                'status' => 'error',
                'trans' => __('cart.error')
            ];
        }
        
        return response()->json(['response' => $response]);
    }
    
    public function remove(Request $request)
    {
        $product = Product::find($request->product_id);
        if ($product) {
            \Cart::removeFromCart($product);
        }
    }
    
    public function show()
    {
        return \Cart::getCart();
    }
    
    public function generateCartHtml()
    {
        $cart_content = $this->show();
        $cart_html = view('components.cart_template', compact('cart_content'))
            ->render();
        
        return response()->json(['cart_html' => $cart_html]);
    }
}
