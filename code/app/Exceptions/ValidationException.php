<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
class ValidationException extends Exception
{
    private Validator $validator;
    protected int $httpResponseCode = 422;

    public function __construct(Validator $validator)
    {
        parent::__construct();
        $this->validator = $validator;
    }

    public function render(): ApiResponse
    {

        $response = new ApiResponse('Validation error', $this->httpResponseCode);
        $response->setErrors($this->validator->errors());
        return $response;
    }
}
