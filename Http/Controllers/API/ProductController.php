<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\API\AbstractApiController;
use App\Models\ProductColor;

class ProductController extends AbstractApiController
{
    private function allowedSorts()
    {
        return [
            'id',
            'name',
            'created_at',
            'updated_at',
        ];
    }
    
    private function allowedIncludes()
	{
		return [
            'color',
		];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $skip = $request->has('skip') ? $request->skip : $this->skip;
            $take = $request->has('take') ? ($request->take < $this->limit ? $request->take : $this->take) : $this->take;

            $products = QueryBuilder::for(Product::class, $request)
                ->allowedSorts($this->allowedSorts())
                ->allowedIncludes($this->allowedIncludes())
                ->skip($skip)
                ->take($take)
                ->get();

            return $products;
        }
        catch(\Exception $exception){
            $data = ['exception' => $exception->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sku' => 'required|max:100|unique:products',
                'name' => 'max:255',
                'price' => 'regex:/^\d{1,8}(\.\d{1,2})?$/',
                'height' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'length' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'width' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'volume' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'weight' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'product_color_id' => 'integer|exists:product_colors,id',
                'image_prefix' => 'max:100',
                'available' => 'boolean',
                'disabled' => 'boolean',
            ]);

            if($validator->fails()){
                throw new \Exception("ValidationException");
            }

            $product = new Product();

            DB::transaction(function () use ($request, $product) {
                $product->fill($request->all())->save();
            });

            return $this->createdResponse($product);
        }
        catch(\Exception $exception){
            $data = ['form_validations' => $validator->errors(), 'exception' => $exception->getMessage()];
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
            $product = QueryBuilder::for(Product::where('id', $id), $request)
            ->allowedIncludes($this->allowedIncludes())
            ->first();
            
            if (is_null($product)){
                return $this->notFoundResponse();
            }
            
            return $this->showResponse($product);
        }
        catch(\Exception $exception){
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
        if (!$product = Product::find($id)) {
            return $this->notFoundResponse();
        }
        try {
            $validator = Validator::make($request->all(), [
                'sku' => "max:100|unique:products,sku,{$id}",
                'name' => 'max:255',
                'price' => 'regex:/^\d{1,8}(\.\d{1,2})?$/',
                'height' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'length' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'width' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'volume' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'weight' => 'regex:/^\d{1,8}(\.\d{1,4})?$/',
                'product_color_id' => 'integer|exists:product_colors,id',
                'image_prefix' => 'max:100',
                'available' => 'boolean',
                'disabled' => 'boolean',
            ]);

            if($validator->fails()){
                throw new \Exception("ValidationException");
            }

            DB::transaction(function () use ($request, $product) {
                $product->fill($request->all())->save();
            });

            return $this->updatedResponse($product);
        }
        catch(\Exception $exception){
            $data = ['form_validations' => $validator->errors(), 'exception' => $exception->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$product = Product::find($id)) {
			return $this->notFoundResponse();
		}
        $product->delete();
        
		return $this->deletedResponse();
    }
    
    public function productColors()
    {
        $product_colors = ProductColor::get();
        
        return $this->showResponse($product_colors);
    }
}
