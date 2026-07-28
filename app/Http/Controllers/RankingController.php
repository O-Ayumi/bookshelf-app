<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    public function index()
    {
        $rankedBooks = Book::withAvg('reviews', 'rating')->havingNotNull('reviews_avg_rating')->orderBy('reviews_avg_rating', 'desc')->take(10)->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
