<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base Controller
 *
 * Provides common functionality for all controllers including standardized
 * error handling, logging, and response formatting.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Handle exceptions and return appropriate response
     *
     * @param \Exception $e The exception to handle
     * @param bool $redirectBack Whether to redirect back or return JSON
     * @param string $customMessage Custom error message (optional)
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function handleError(
        \Exception $e,
        bool $redirectBack = true,
        string $customMessage = null,
    ) {
        // Log the error with context
        Log::error(get_class($e) . ": " . $e->getMessage(), [
            "exception" => get_class($e),
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine(),
            "trace" => $e->getTraceAsString(),
        ]);

        // Use custom message if provided, otherwise use generic message
        $message = $customMessage ?? "Terjadi kesalahan. Silakan coba lagi.";

        if ($redirectBack) {
            return back()->with("error", $message)->withInput();
        }

        return response()->json(
            [
                "success" => false,
                "message" => $message,
                // Error details only logged, never exposed via API
                "error_id" => substr(md5($e->getMessage() . $e->getFile() . $e->getLine()), 0, 8),
            ],
            500,
        );
    }

    /**
     * Handle validation errors
     *
     * @param \Illuminate\Validation\ValidationException $e
     * @param bool $redirectBack Whether to redirect back or return JSON
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function handleValidationError(
        \Illuminate\Validation\ValidationException $e,
        bool $redirectBack = true,
    ) {
        Log::warning("Validation failed", [
            "errors" => $e->errors(),
            "request" => request()->all(),
        ]);

        if ($redirectBack) {
            return back()->withErrors($e->errors())->withInput();
        }

        return response()->json(
            [
                "success" => false,
                "message" => "Validasi gagal.",
                "errors" => $e->errors(),
            ],
            422,
        );
    }

    /**
     * Handle not found errors
     *
     * @param string $message Custom error message
     * @param bool $redirectBack Whether to redirect back or return JSON
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function handleNotFound(
        string $message = "Data tidak ditemukan.",
        bool $redirectBack = true,
    ) {
        Log::warning("Resource not found", ["message" => $message]);

        if ($redirectBack) {
            return back()->with("error", $message);
        }

        return response()->json(
            [
                "success" => false,
                "message" => $message,
            ],
            404,
        );
    }

    /**
     * Handle unauthorized access
     *
     * @param string $message Custom error message
     * @param bool $redirectBack Whether to redirect back or return JSON
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function handleUnauthorized(
        string $message = "Anda tidak memiliki akses untuk melakukan aksi ini.",
        bool $redirectBack = true,
    ) {
        Log::warning("Unauthorized access attempt", [
            "user_id" => auth()->id(),
            "ip" => request()->ip(),
        ]);

        if ($redirectBack) {
            return back()->with("error", $message);
        }

        return response()->json(
            [
                "success" => false,
                "message" => $message,
            ],
            403,
        );
    }

    /**
     * Standard success response
     *
     * @param string $message Success message
     * @param mixed $data Additional data to return
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(
        string $message = "Operasi berhasil.",
        $data = null,
    ) {
        return response()->json([
            "success" => true,
            "message" => $message,
            "data" => $data,
        ]);
    }

    /**
     * Standard error response
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(
        string $message = "Operasi gagal.",
        int $code = 500,
    ) {
        return response()->json(
            [
                "success" => false,
                "message" => $message,
            ],
            $code,
        );
    }
}
