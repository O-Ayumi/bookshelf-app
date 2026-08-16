<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

Route::middleware(['auth'])->group(function () {
    Route::resource('books', BookController::class);
    Route::get('/books/isbn/{isbn}', [BookController::class, 'fetchBookByIsbn'])->name('books.fetch_isbn');

    Route::resource('genres', GenreController::class);

    Route::post('books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::get('reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');

    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('books/{book}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::post('reviews/{review}/like', [LikeController::class, 'toggle'])->name('reviews.like');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::resource('reading-plans', ReadingPlanController::class);
    Route::post('reading-plans/{reading_plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
    Route::patch('reading-plans/{readingPlan}/start', [ReadingPlanController::class, 'start'])->name('reading-plans.start');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
});
