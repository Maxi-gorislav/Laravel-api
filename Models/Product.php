<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use tiagomichaelsousa\LaravelFilters\Traits\Filterable;
use App\Images\ImageHelper;

class Product extends Model
{
    use HasFactory, Filterable;
    
    protected $fillable = [
        'sku',
        'name',
        'price',
        'height',
        'length',
        'width',
        'volume',
        'weight',
        'image_prefix',
        'available',
        'disabled',
    ];
    
    protected $appends = ['product_image', 'product_image_small', 'product_image_gallery'];
    
    public function orders()
    {
        return $this->belongsToMany('App\Models\Order', 'order_product');
    }
    
    public function color()
    {
        return $this->hasOne('App\Models\ProductColor', 'id', 'product_color_id');
    }
    
    public function getProductImageAttribute()
	{
		return ImageHelper::getProductImage($this);
    }
    
    public function getProductImageSmallAttribute()
	{
		return ImageHelper::getProductImageSmall($this);
    }
    
    public function getProductImageGalleryAttribute()
	{
		return ImageHelper::getProductImageGallery($this);
	}
}
