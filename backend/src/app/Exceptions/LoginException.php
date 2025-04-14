<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;

class LoginException extends Exception
{
    public function __construct(string $message = "", int $code = 422, ?Throwable $previous = null)
    {
        $message = empty($message) ? (Lang::has('exception.login') ? trans('exception.login') : trans('Login or password incorrect')) : $message;
        parent::__construct($message, $code, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
        ], $this->code);
    }
}
