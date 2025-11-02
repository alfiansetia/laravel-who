<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OdooException extends Exception
{
    protected $statusCode;
    protected $responseBody;

    public function __construct($message = 'Odoo Error!', $statusCode = 500, $responseBody = null)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'body' => $this->responseBody, // tampilkan isi JSON error dari Odoo
            ], $this->statusCode);
        }

        return redirect()->route('home')->with('error', $this->getMessage());
    }

    public function report(): void
    {
        // Log::error('OdooException', [
        //     'message' => $this->getMessage(),
        //     'status' => $this->statusCode,
        //     'body' => $this->responseBody,
        // ]);
    }
}
