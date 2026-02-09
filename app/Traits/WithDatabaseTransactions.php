<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * WithDatabaseTransactions Trait
 *
 * Provides a consistent way to handle database transactions across controllers.
 * Eliminates duplicate transaction handling code and ensures proper error logging.
 *
 * Usage:
 *   return $this->runInTransaction(function () {
 *       // ... database operations
 *       return redirect()->with('success', 'Operation completed');
 *   }, 'user.store');
 *
 * @package App\Traits
 */
trait WithDatabaseTransactions
{
    /**
     * Execute a callback within a database transaction.
     *
     * Automatically handles commit/rollback and error logging.
     * If the callback throws an exception, the transaction is rolled back,
     * the error is logged, and the exception is re-thrown.
     *
     * @param callable $callback The operations to execute within the transaction
     * @param string $operationName Human-readable operation name for logging
     * @return mixed The return value of the callback
     * @throws Throwable If the callback throws an exception
     */
    protected function runInTransaction(callable $callback, string $operationName = 'operation')
    {
        DB::beginTransaction();

        try {
            $result = $callback();
            DB::commit();

            // Log successful transaction for audit trail
            Log::info("{$operationName} completed successfully", [
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            // Log detailed error information
            Log::error("{$operationName} failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'request_data' => request->except(['password', 'password_confirmation', '_token']),
            ]);

            // Re-throw the exception for controller-level handling
            throw $e;
        }
    }

    /**
     * Execute a callback within a database transaction with custom error handling.
     *
     * Unlike runInTransaction, this method catches exceptions and returns
     * a formatted error response instead of throwing.
     *
     * @param callable $callback The operations to execute within the transaction
     * @param string $operationName Human-readable operation name for logging
     * @param string $successMessage Message to return on success
     * @param string $errorMessage Message to return on failure
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function runInTransactionWithErrorHandling(
        callable $callback,
        string $operationName = 'operation',
        string $successMessage = 'Operation completed successfully',
        string $errorMessage = 'An error occurred'
    ) {
        DB::beginTransaction();

        try {
            $result = $callback();
            DB::commit();

            Log::info("{$operationName} completed successfully", [
                'user_id' => auth()->id(),
            ]);

            // Return appropriate response based on request type
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => $result ?? null,
                ]);
            }

            return redirect()
                ->back()
                ->with('success', $successMessage);
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error("{$operationName} failed", [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            // Return appropriate error response
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => app()->environment('local') ? $e->getMessage() : null,
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', $errorMessage . ': ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Check if current request is within an active transaction.
     *
     * Useful for nested operations that should not start their own transaction.
     *
     * @return bool True if within a transaction, false otherwise
     */
    protected function isInTransaction(): bool
    {
        return DB::transactionLevel() > 0;
    }
}
