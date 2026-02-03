<?php

use App\Http\Controllers\AboutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Страница информации о проекте
Route::get('/about', [AboutController::class, 'index'])->name('about');

// Страница Pre-Commit (инструменты и команды перед коммитом)
Route::get('/pre-commit', [AboutController::class, 'preCommit'])->name('pre-commit');
