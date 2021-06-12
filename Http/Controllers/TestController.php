<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;

class TestController extends Controller
{
    public function test()
    {
        // $users = DB::connection('mysql-seed')
        //     ->table('customers')
        //     ->get();
        // $users_bulk_insert = [];
        // foreach ($users as $user) {
        //     $users_bulk_insert[] = [
        //         'role_id' => 4,
        //         'name' => $user->last_name,
        //         'login' => $user->first_name,
        //         'password' => $user->password,
        //         'created_at' => $user->created_at,
        //         'updated_at' => $user->updated_at,
        //     ];
        // }
        
        // User::insert($users_bulk_insert);
        
        $statuses = [
            'pending' => 1, 'cancel' => 2, 'loaded' => 3, 'reserved' => 4
        ];
        $orders = DB::connection('mysql-seed')
            ->table('orders')
            ->leftJoin('cart', 'orders.cart_id', '=', 'cart.id')
            ->leftJoin('cart_items', 'cart.id', '=', 'cart_items.cart_id')
            ->leftJoin('customers', 'cart.customer_id', '=', 'customers.id')
            ->leftJoin('products', 'products.id', '=', 'cart_items.product_id')
            ->select('orders.status', 'products.sku', 'customers.first_name', 'orders.deleted_at', 'orders.customer_id', 'orders.created_at', 'orders.updated_at', 'orders.id', 'cart_items.product_id')
            ->where('orders.cart_id', '!=', null)
            ->where('cart.customer_id', '!=', null)
            ->where('cart_items.product_id', '!=', null)
            ->get();
            
        $orders_bulk_insert = [];
        $order_product_bulk_insert = [];
        foreach ($orders->groupBy(['id']) as $order_key => $order) {
            foreach($order as $sub_order_key => $sub_order) {
                $product = Product::where('sku', $sub_order->sku)->first();
                $order_product_bulk_insert[] = [
                    'order_id' => $order_key,
                    'product_id' => $product->id,
                    'updated_at' => $sub_order->updated_at,
                    'deleted_at' => $sub_order->deleted_at,
                ];
                
                if ($sub_order_key == 0) {
                    $user = User::where('login', $sub_order->first_name)->first();
                    $orders_bulk_insert[] = [
                        'id' => $order_key,
                        'order_status_id' => $statuses[$sub_order->status],
                        'user_id' => $user->id,
                        'created_at' => $sub_order->created_at,
                        'updated_at' => $sub_order->updated_at,
                        'deleted_at' => $sub_order->deleted_at,
                    ];
                }
            }
        }
        // dd($order_product_bulk_insert);
        dd($orders_bulk_insert);
    }
    
    public function updateProducts()
    {
        $sold_product_skus = explode("\n",file_get_contents(asset('sku.txt')));
        Product::whereIn('sku', $sold_product_skus)->update([
            'available' => 0,
        ]);
    }
}
