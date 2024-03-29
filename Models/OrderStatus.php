<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;
    
    public function orders()
    {
        return $this->hasMany('App\Models\Order', 'order_status_id', 'id');
    }
}
