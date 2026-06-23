<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function successResponse($data = null, $message = 'Success', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $statusCode);
    }
    
    protected function validationErrorResponse($errors): JsonResponse
    {
        return $this->errorResponse('Validation failed', 422, $errors);
    }
}
