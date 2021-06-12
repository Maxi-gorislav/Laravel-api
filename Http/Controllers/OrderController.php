<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use App\Services\OrderService;
use App\Classes\Enums\OrderStatus;
use App\Models\OrderStatus as OrderStatusModel;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $user_service;
    private $order_service;
    
    public function __construct()
    {
        $this->user_service = new UserService;
        $this->order_service = new OrderService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $request->query();
        $catalog_url = $request->session()->get('home_full_url', route('home'));
        $order_statuses = OrderStatusModel::get();
        $is_web_admin = $this->user_service->info()->isWebAdmin();
        $orders_qb = Order::select('*');
        if (!$this->user_service->info()->isWebAdmin()) {
            $orders_qb->where('user_id', $this->user_service->info()->id);
        }
        if (isset($query['status']) && in_array($query['status'], $order_statuses->pluck('id')->toArray())) {
            $orders_qb->where('order_status_id', $request->status);
        }
        $orders_qb->with(['orderStatus', 'products', 'user'])
            ->orderBy('created_at', 'desc');
        $orders = $orders_qb->paginate(9);

        return view('orders.index', compact('orders', 'is_web_admin', 'query', 'order_statuses', 'catalog_url'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $current_cart = \Cart::getCart();
        $current_cart_product_ids = [];
        $products_from_cart_available = [];
        $order_product_bulk = [];
        
        if (empty($current_cart)) {
            $response = [
                'status' => 'cart_is_empty',
                'trans' => __('cart.cart_is_empty')
            ];
            return response()->json(['response' => $response]);
        }
        try {
            $order = new Order;
            DB::transaction(function () use (
                    $order, 
                    $current_cart, 
                    $current_cart_product_ids, 
                    $products_from_cart_available, 
                    $order_product_bulk) {
                $order->order_status_id = config('initial-values.default_order_status_id');
                $order->user_id = $this->user_service->info()->id;
                $order->save();
            
                //get ids of products from cart
                foreach ($current_cart as $product) {
                    $current_cart_product_ids[] = $product->id;
                }
                $products_from_cart = Product::whereIn('id', $current_cart_product_ids)
                    ->get();
                //check if those products are available at the time of purchase and get only available products ids
                $products_from_cart_available = $this->order_service->availableProductIds($products_from_cart);
                
                foreach ($products_from_cart_available as $product_id) {
                    $order_product_bulk[] = [
                        'order_id' => $order->id,
                        'product_id' => $product_id
                    ];
                }
                
                OrderProduct::insert($order_product_bulk);
                Product::whereIn('id', $products_from_cart_available)
                    ->update(['available' => 0]);
			});
            \Cart::clearCart();
            $response = [
                'status' => 'ok',
                'url' => route('orders.index')
            ];
        } catch (\Exception $exception) {
            $response = [
                'status' => '500',
                'trans' => __('cart.error')
            ];
            
            return response()->json(['response' => $response]);
        }
        
        return response()->json(['response' => $response]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::with('products')
            ->find($id);
        if (!$order) {
            abort(404);
        }
        if (!$this->user_service->info()->isWebAdmin() && $order->user_id !== $this->user_service->info()->id) {
            abort(404);
        }
        $pending_status = OrderStatusModel::where('name', OrderStatus::PENDING)->first();
        if ($order->order_status_id == $pending_status->id) {
            $cancelconfirmed_status = OrderStatusModel::where('name', OrderStatus::CANCELCONFIRMED)->first();
            $order->order_status_id = $cancelconfirmed_status->id;
            $product_ids = $order->products->pluck('id')->toArray();
            Product::whereIn('id', $product_ids)
                ->update(['available' => 1]);
        } else {
            $cancel_status = OrderStatusModel::where('name', OrderStatus::CANCEL)->first();
            $order->order_status_id = $cancel_status->id;
        }
        
        $order->save();
        $order->delete();
        
        return back()->with(['success' => __('flash_messages.order_deleted')]);;
    }
    
    public function reserve($id)
    {
        $product = Product::find($id);
        if (!$product) {
            abort(404);
        }
        if (!$this->order_service->checkIfProductAvailable($product)) {
            return back()->with(['info' => __('flash_messages.product_unavailable')]);
        }
        $reserved_status = OrderStatusModel::where('name', OrderStatus::RESERVED)
            ->first();
        try {
            DB::transaction(function () use ($product, $reserved_status) {
                $product->update([
                    'available' => 0
                ]);
                $order = new Order;
                $order->user_id = $this->user_service->info()->id;
                $order->order_status_id = $reserved_status->id;
                $order->save();
                
                $order_product = new OrderProduct;
                $order_product->order_id = $order->id;
                $order_product->product_id = $product->id;
                $order_product->save();
            });
            
            return redirect()->route('orders.index');
        } catch (\Exception $exception) {
            return back()->with(['error' => __('flash_messages.error')]);
        }
    }
}
