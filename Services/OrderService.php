<?php

namespace App\Services;

use App\Models\Product;

class OrderService
{
    //return only available product ids from array of Product $product
    public function availableProductIds($products)
    {
        $available_product_ids = $products->filter(function ($product) {
            return $product->available;
        })->pluck('id')->toArray();
        
        return $available_product_ids;
    }
    
    
    public function checkIfProductAvailable(Product $product)
    {
        if ($product->available) {
            return true;
        }
        
        return false;
    }
}
