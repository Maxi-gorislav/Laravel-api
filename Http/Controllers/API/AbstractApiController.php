<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Classes\Enums\ApiDefaults;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\ApiLog;

abstract class AbstractApiController extends Controller
{
    protected $take = ApiDefaults::DEFAULT_TAKE_VALUE;
    protected $skip = ApiDefaults::DEFAULT_SKIP_VALUE;
	protected $limit = ApiDefaults::DEFAULT_LIMIT_VALUE;
	
    protected function createdResponse($data)
	{
		$response = [
			'code' => 201,
			'status' => 'success',
			'data' => [
				'id' => $data->id,
			]
		];
		
		return $this->returnResponse($response);
    }
    
    protected function clientErrorResponse($data)
	{
		$response = [
			'code' => 422,
			'status' => 'error',
			'data' => $data,
			'message' => 'Unprocessable entity'
		];
		
		return $this->returnResponse($response);
    }
    
    protected function notFoundResponse()
	{
		$response = [
			'code' => 404,
			'status' => 'error',
			'data' => 'Resource Not Found',
			'message' => 'Not Found'
		];
		
		return $this->returnResponse($response);
    }
    
    protected function deletedResponse()
	{
		$response = [
			'code' => 200,
			'status' => 'success',
			'data' => [],
			'message' => 'Resource deleted'
        ];
        
		return $this->returnResponse($response);
	}
	
	protected function updatedResponse($data)
	{
		$response = [
			'code' => 200,
			'status' => 'success',
			'data' => $data
		];
		
		return $this->returnResponse($response);
	}

	protected function showResponse($data)
	{
		$response = [
			'code' => 200,
			'status' => 'success',
			'data' => $data
		];
		
		return $this->returnResponse($response);
	}
	
	protected function returnResponse($response)
	{
		$this->saveApiLog($response);
		
		return response()->json($response, $response['code']);
	}

	protected function saveApiLog($response)
	{
		$ts = Carbon::now();
		$response = [
			'code' => $response['code'],
			'status' => $response['status'],
			'message' => $response['message'] ?? '',
			'data' => $response['status'] == 'error' ? $response['data'] : [],
		];

		$request = [
			'user' => Auth::user(),
			'method' => Request::method(),
			'url' => Request::fullUrl(),
			'content' => json_decode(Request::getContent(), true)
		];

		$api_log = new ApiLog();
		$api_log->api_user_id = Request::user()->id;
		$api_log->method = Request::method();
		$api_log->setAttribute('query', Request::fullUrl());
		$api_log->params = json_encode(Request::all());
		$api_log->date = $ts;
		$api_log->response = json_encode($response, JSON_UNESCAPED_UNICODE);
		$api_log->save();

		$user_id = Auth::user()->id;

		Storage::disk('api_logs')
			->put("user_id_{$user_id}/{$ts->year}/{$ts->month}/{$ts->day}/{$response['code']}_{$ts->timestamp}_{$ts->format('Y-m-d_H-i-s')}.json", json_encode([
				'request' => $request,
				'response' => $response,
			], JSON_PRETTY_PRINT));
	}
}