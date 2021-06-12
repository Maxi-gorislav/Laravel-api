<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\AbstractApiController;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerController extends AbstractApiController
{
    private function allowedIncludes()
	{
		return [
			'orders.products'
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
            $users = QueryBuilder::for(User::where('role_id', config('initial-values.web_default_role_id')), $request)
                ->allowedIncludes($this->allowedIncludes())
                ->skip($skip)
                ->take($take)
                ->get();

            return $users;
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
				'login' => 'required|max:25|unique:users',
				'password' => 'required|min:6',
				'name' => 'required|max:255',
				'email' => 'max:255|email|unique:users'
			]);

			$user = new User();

			DB::transaction(function () use ($request, $user) {
                $user->password = Hash::make($request->input('password'));
                $user->fill($request->all())->save();
			});

			return $this->createdResponse($user);
		} catch (\Exception $exception) {
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
            $user = QueryBuilder::for(User::where('id', $id), $request)
                ->allowedIncludes($this->allowedIncludes())
                ->first();

            if (is_null($user) || !$user->isWebDefault()){
                return $this->notFoundResponse();
            }
            
            return $this->showResponse($user);
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
        try {
            $user = User::find($id);
			if (is_null($user) || !$user->isWebDefault()) {
				return $this->notFoundResponse();
            }
              
			$validator = Validator::make($request->all(), [
				'login' => "max:25|unique:users,login,{$id}",
				'password' => 'min:6',
				'name' => 'max:255',
				'email' => "max:255|email|unique:users,email,{$id}"
            ]);
            
			DB::transaction(function () use ($request, $user) {
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->input('password'));
                }
                $user->fill($request->all())->save();
			});

			return $this->updatedResponse($user);
		} catch (\Exception $exception) {
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
        $user = User::find($id);
        if (is_null($user) || !$user->isWebDefault()) {
			return $this->notFoundResponse();
		}
        $user->delete();
        
		return $this->deletedResponse();
    }
}
