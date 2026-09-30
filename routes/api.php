<?php

use App\Http\Controllers\Api\CourseController;
use Illuminate\Support\Facades\Route;

Route::get('/courses', [CourseController::class, 'index'])
    ->middleware(['web', 'auth:web']);

Route::post('/courses', [CourseController::class, 'store'])
    ->middleware(['web', 'auth:web']);

// untuk ada id
Route::get('/courses/{course}', [CourseController::class, 'show'])
    ->middleware(['web', 'auth:web']);

Route::patch('/courses/{course}', [CourseController::class, 'update'])
    ->middleware(['web', 'auth:web']);

Route::delete('/courses/{course}', [CourseController::class, 'destroy'])
    ->middleware(['web', 'auth:web']);
