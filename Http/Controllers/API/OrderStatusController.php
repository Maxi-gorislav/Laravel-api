<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\AbstractApiController;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class OrderStatusController extends AbstractApiController
{
    private function allowedIncludes()
	{
		return [
            'orders.products',
		];
    }
    
    public function index(Request $request)
    {
        $skip = $request->has('skip') ? $request->skip : $this->skip;
        $take = $request->has('take') ? ($request->take < $this->limit ? $request->take : $this->take) : $this->take;
        
        try {
            $order_statuses = QueryBuilder::for(OrderStatus::class, $request)
                ->allowedIncludes($this->allowedIncludes())
                ->skip($skip)
                ->take($take)
                ->get();

            return $order_statuses;
        }
        catch(\Exception $exception){
            $data = ['exception' => $exception->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }
}
