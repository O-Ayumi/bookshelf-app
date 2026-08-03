<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    public function index()
    {
        $rankedBooks = Book::has('reviews')->withAvg('reviews', 'rating')->withCount('reviews')->orderBy('reviews_avg_rating', 'desc')->take(10)->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
