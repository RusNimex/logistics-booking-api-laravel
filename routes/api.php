<?php

use App\Http\Controllers\Slots\AvailabilityController;
use App\Http\Controllers\Slots\HoldController;
use Illuminate\Support\Facades\Route;

Route::get('/slots/availability', AvailabilityController::class);
Route::post('/slots/{id}/hold', [HoldController::class, 'create'])
    ->middleware('idempotency');
Route::post('/holds/{id}/confirm', [HoldController::class, 'confirm']);
Route::delete('/holds/{id}', [HoldController::class, 'cancel']);
