<?php

use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cgpatracker', Dashboard::class)
    ->middleware('auth')
    ->name('dashboard');
