<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;

Route::get('/', fn () => redirect()->route('feedback.index'));

Route::get('/feedbacks', [FeedbackController::class, 'index'])->name('feedback.index');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
Route::patch('/feedback/{feedback}/status', [FeedbackController::class, 'updateStatus'])->name('feedback.update');
