<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'order_product');
    }
    
    public function orderStatus()
    {
        return $this->hasOne('App\Models\OrderStatus', 'id', 'order_status_id');
    }
    
    public function scopeOrderStatusIdEquals(Builder $query, $order_status_id): Builder
    {
        return $query->where('order_status_id', '=', $order_status_id);
    }
}
