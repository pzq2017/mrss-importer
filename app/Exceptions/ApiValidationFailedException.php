<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\Validator;

class ApiValidationFailedException extends Exception
{
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function response()
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->validator->errors()->getMessages(),
        ], 200);
    }
}
