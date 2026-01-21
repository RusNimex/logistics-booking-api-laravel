<?php

use App\Http\Controllers\Slots\AvailabilityController;
use App\Http\Controllers\Slots\HoldController;
use Illuminate\Support\Facades\Route;

Route::get('/slots/availability', AvailabilityController::class);
Route::post('/slots/{id}/hold', [HoldController::class, 'create'])
    ->middleware('idempotency');
Route::post('/slots/{id}/confirm', [HoldController::class, 'confirm']);
Route::post('/slots/{id}/cancel', [HoldController::class, 'canceled']);
