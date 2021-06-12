<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $fillable = [
		'api_user_id',
		'query',
		'params',
		'date',
	];
}
