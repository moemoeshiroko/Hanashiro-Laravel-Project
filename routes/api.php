<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// GeoJSON API
Route::prefix('geojson')->name('api.')->group(function () {
    Route::get('/points', [ApiController::class, 'points'])->name('points');
    Route::get('/polylines', [ApiController::class, 'polylines'])->name('polylines');
    Route::get('/polygons', [ApiController::class, 'polygons'])->name('polygons');
});
