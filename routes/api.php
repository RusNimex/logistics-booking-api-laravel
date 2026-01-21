<?php

use App\Http\Controllers\Slots\AvailabilityController;
use Illuminate\Support\Facades\Route;

Route::get('/slots/availability', AvailabilityController::class);
