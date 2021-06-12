<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Filters\ProductFilter;
use Illuminate\Http\Request;
use App\Filters\FilterData;
use App\Models\ProductColor;
use Illuminate\Support\Facades\URL;

class CatalogController extends Controller
{
    public function index(ProductFilter $filters, Request $request)
    {
        $query = $request->query();
        $request->session()->put('home_full_url', Url::full());
        $filter_data = FilterData::init($query);
        $product_colors = ProductColor::get();
        $products = Product::filter($filters)
            ->where('volume', '>', '0')
            ->where('available', 1)
            ->orderBy('name', 'asc')
            ->paginate(9);

        return view('catalog.index', compact('products', 'query', 'filter_data', 'product_colors'));
    }
    
    public function product($id, Request $request)
    {
        $product = Product::with('color')
            ->find($id);
        if (!$product) {
            abort(404);
        }
        $catalog_url = $request->session()->get('home_full_url', route('home'));
        
        return view('catalog.product', compact('product', 'catalog_url'));
    }
}
