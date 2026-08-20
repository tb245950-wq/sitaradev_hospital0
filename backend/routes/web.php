<?php

use Illuminate\Support\Facades\Route;

// Arahkan ke route yang akan merender aplikasi Vue SPA
Route::get('/{any?}', function () {
    return view('welcome'); // Pastikan welcome.blade.php memuat aplikasi Vue/Vite
})->where('any', '.*');
