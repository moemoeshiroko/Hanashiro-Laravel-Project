<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\PolylineController;
use App\Http\Controllers\PolygonController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/welcome', function () {
    return redirect()->route('welcome');
});

Route::get('/map', [MapController::class, 'index'])->name('map');
Route::get('/map/{type}/{id}', [MapController::class, 'index'])->name('map.focus');
Route::get('/points/{id}', [MapController::class, 'index'])->defaults('type', 'Point')->name('points.focus');
Route::get('/polylines/{id}', [MapController::class, 'index'])->defaults('type', 'Polyline')->name('polylines.focus');
Route::get('/polygons/{id}', [MapController::class, 'index'])->defaults('type', 'Polygon')->name('polygons.focus');
Route::get('/table', [MapController::class, 'table'])->name('table');
Route::middleware('auth')->group(function () {
    Route::delete('/map/delete-all', [MapController::class, 'deleteAll'])->name('map.delete-all');

    Route::prefix('points')->group(function () {
        Route::post('/store', [PointController::class, 'store'])->name('points.store');
        Route::patch('/{id}', [PointController::class, 'update'])->name('points.update');
        Route::delete('/{id}', [PointController::class, 'destroy'])->name('points.destroy');
    });

    Route::prefix('polylines')->group(function () {
        Route::post('/store', [PolylineController::class, 'store'])->name('polylines.store');
        Route::patch('/{id}', [PolylineController::class, 'update'])->name('polylines.update');
        Route::delete('/{id}', [PolylineController::class, 'destroy'])->name('polylines.destroy');
    });

    Route::prefix('polygons')->group(function () {
        Route::post('/store', [PolygonController::class, 'store'])->name('polygons.store');
        Route::patch('/{id}', [PolygonController::class, 'update'])->name('polygons.update');
        Route::delete('/{id}', [PolygonController::class, 'destroy'])->name('polygons.destroy');
    });
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
