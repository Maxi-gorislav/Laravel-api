<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\AbstractApiController;
use Spatie\QueryBuilder\QueryBuilder;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class OrderController extends AbstractApiController
{
    private function allowedIncludes()
	{
		return [
            'products',
            'orderstatus'
		];
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $skip = $request->has('skip') ? $request->skip : $this->skip;
        $take = $request->has('take') ? ($request->take < $this->limit ? $request->take : $this->take) : $this->take;

        try {
			$orders = QueryBuilder::for(Order::class, $request)
				->allowedIncludes($this->allowedIncludes())
				->allowedFilters([
					AllowedFilter::scope('order_status_id_equals'),
				])
                ->skip($skip)
				->take($take)
				->withTrashed()
                ->get();
                
			return $orders;
		} catch (\Exception $exception) {
			$data = ['exception' => $exception->getMessage()];
			return $this->clientErrorResponse($data);
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
			$orders = QueryBuilder::for(Order::where('id', $id), $request)
				->allowedIncludes($this->allowedIncludes())
				->withTrashed()
				->first();

			if (!$orders) {
				return $this->notFoundResponse();
			}

			return $this->showResponse($orders);
		} catch (\Exception $exception) {
			$data = ['exception' => $exception->getMessage()];
			return $this->clientErrorResponse($data);
		}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
			if (!$order = Order::withTrashed()->find($id)) {
				return $this->notFoundResponse();
			}

			$validator = Validator::make($request->all(), [
				'order_status_id' => 'required|exists:order_statuses,id',
			]);

			if ($validator->fails()) {
				throw new \Exception("ValidationException");
			}

			DB::transaction(function () use ($request, $order) {
				$order->order_status_id = $request->input('order_status_id');
				$order->save();
			});

			return $this->updatedResponse($order);
		} catch (\Exception $exception) {
			$data = ['form_validations' => $validator->errors(), 'exception' => $exception->getMessage()];
			return $this->clientErrorResponse($data);
		}
    }
}
