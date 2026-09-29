<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;

Route::get('/courses', [CourseController::class, 'index'])
    ->middleware(['web', 'auth:web']);

Route::post('/courses', [CourseController::class, 'store'])
    ->middleware(['web', 'auth:web']);