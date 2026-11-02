<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\PostSubmissionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('/assessment/section-1', [PostController::class, 'index'])->name('assessment.section1');
Route::get('/assessment/section-3', [PostSubmissionController::class, 'create'])->name('assessment.section3');
Route::post('/assessment/section-3', [PostSubmissionController::class, 'store'])->name('assessment.submit');
