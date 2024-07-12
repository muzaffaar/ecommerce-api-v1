<?php

use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'can:curier', 'verified'])->prefix('curier')->group(function(){
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assignCourier'])->name('curier.tasks.assignCourier');
    Route::post('/tasks/{task}/unassign', [TaskController::class, 'unassignCourier'])->name('curier.tasks.unassignCourier');
    Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('curier.tasks.updateStatus');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('curier.tasks.show');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'completeTask'])->name('curier.tasks.completeTask');
});
