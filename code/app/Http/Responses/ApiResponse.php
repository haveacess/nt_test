<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;

class ApiResponse implements Responsable
{
    private int $httpCode;
    private string $message;
    private MessageBag $errors;
    private array $data = [];

    /**
     * Create new api response
     *
     * @param string $message Any inform message.
     * ex: User created successful
     * @param int $httpCode Http status code for response
     */
    public function __construct(string $message, int $httpCode = 200)
    {
        $this->errors = new MessageBag();
        $this->httpCode = $httpCode;
        $this->message = $message;
    }

    /**
     * Set some errors for response object
     *
     * @param MessageBag $errors Errors from validator or other places
     * @return void
     */
    public function setErrors(MessageBag $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Set any data to response
     *
     * @param mixed $data New resources or something else
     * @return void
     */
    public function setData(mixed $data)
    {
        $this->data = $data;
    }

    public function toResponse($request): JsonResponse
    {
        $response = [
            "status_code" => $this->httpCode,
            "message" => $this->message,
        ];

        if ($this->errors->isNotEmpty()) {
            $response["errors"] = $this->errors->toArray();
        }

        if ($this->data) {
            $response['data'] = $this->data;
        }

        return response()->json($response, $this->httpCode);
    }
}
