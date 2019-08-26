<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiValidationFailedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function validateWithException(Request $request, array $rules, array $messages = [])
    {
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new ApiValidationFailedException($validator);
        }
    }

    public function responseErrorWithMessage($message)
    {
        return response()->json([
            'status' => 'error',
            'message'=> $message,
        ], 200);
    }

    public function responseSuccessWithMessage($message='')
    {
        return response()->json([
            'status' => 'success',
            'message'=> $message,
        ], 200);
    }
}
