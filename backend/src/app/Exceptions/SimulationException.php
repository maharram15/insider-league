<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class SimulationException extends Exception
{

    public function report(): bool
    {
        return false;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage() ?: 'An error occurred during simulation.',
        ], 500);
    }
    public static function handle(Exception $e, string $contextMessage): array
    {
        Log::error($contextMessage . ': ' . $e->getMessage());
        Log::error($e->getTraceAsString());

        return [
            'success' => false,
            'message' => $contextMessage . ': ' . $e->getMessage(),
        ];
    }
}
