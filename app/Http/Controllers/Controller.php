<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendNotFound()
    {
        return $this->sendResponse(null, 'Not Found!', 404);
    }

    public function sendResponse($data = null, $message = '', $code = 200)
    {
        if (is_string($data) && empty($message)) {
            $message = $data;
            $data = null;
        }
        if (is_array($data) && (Arr::exists($data, 'data') || Arr::exists($data, 'message'))) {
            return response()->json($data, $code);
        }

        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public function sendError($message, $code = 500)
    {
        return $this->sendResponse(null, $message, $code);
    }
}
