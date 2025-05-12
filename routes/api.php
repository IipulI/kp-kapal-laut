<?php

use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\API\TeacherController; // If you have one

// Auth routes (as before)
Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});


Route::group(['middleware' => ['auth:api', 'role:admin'], 'prefix' => '/admin'], function () {
    Route::get('inventories', [InventoryController::class, 'index']);
    Route::get('inventory/{id}', [InventoryController::class, 'show']);
    Route::post('inventory', [InventoryController::class, 'store']);
    Route::put('inventory/{id}', [InventoryController::class, 'update']);
    Route::delete('inventory/{id}', [InventoryController::class, 'destroy']);
});
