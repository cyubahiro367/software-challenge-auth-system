<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function successMessage(string $message, ?array $data = [])
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'timestamp' => now()->format('Y-m-d'),
            'data' => $data,
        ]);
    }
}
