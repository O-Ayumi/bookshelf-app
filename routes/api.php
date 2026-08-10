<?php

use App\Http\Controllers\Api\V1\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// routes/api.php の一番上に追記
Route::get('test-header', function (Request $request) {
    return response()->json([
        'all_headers' => $request->headers->all(),
        'has_auth_header' => $request->hasHeader('Authorization'),
        'token_received' => $request->bearerToken(),
    ]);
});

Route::prefix('v1')->group(function () {
    Route::apiResource('books', BookController::class)->only(['index', 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('books', BookController::class)->only(['store', 'update', 'destroy']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
