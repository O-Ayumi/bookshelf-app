<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
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

Route::middleware(['auth'])->group(function () {
    Route::resource('books', BookController::class);
    Route::resource('genres', GenreController::class);

    Route::get('/ranking', function () {
        return 'ランキング画面（未実装）';
    })->name('ranking.index');
    Route::get('/favorites', function () {
        return 'お気に入り画面（未実装）';
    })->name('favorites.index');

    // ログアウトはPOSTメソッドで要求されることが多いので、両方に対応できるようにするか、一般的なPOSTで定義します
    Route::post('/logout', function () {
        return 'ログアウト処理（未実装）';
    })->name('logout');
    Route::post('/books/{book}/favorite', function () {
        return back()->with('success', 'お気に入りを切り替えました（ダミー）');
    })->name('favorites.toggle');
    Route::post('/books/{book}/reviews', function () {
        return back()->with('success', 'レビューを投稿しました（ダミー）');
    })->name('reviews.store');
});
