<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $summaryRaw = Review::where('user_id', $user->id)
            ->selectRaw('
                COUNT(id) as total_reviews,
                COUNT(DISTINCT book_id) as books_read,
                AVG(rating) as average_rating
            ')
            ->first();

        $summary = [
            'total_reviews' => $summaryRaw->total_reviews ?? 0,
            'books_read' => $summaryRaw->books_read ?? 0,
            'average_rating' => $summaryRaw->average_rating ?? 0.0,
        ];

        $distributionRaw = Review::where('user_id', $user->id)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $ratingDistribution = collect([
            1 => $distributionRaw->get(1, 0),
            2 => $distributionRaw->get(2, 0),
            3 => $distributionRaw->get(3, 0),
            4 => $distributionRaw->get(4, 0),
            5 => $distributionRaw->get(5, 0),
        ]);

        $topRatedBooks = Review::where('user_id', $user->id)
            ->where('rating', '>=', 4)
            ->with('book')
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $genreRatings = Review::where('reviews.user_id', $user->id)
            ->join('books', 'reviews.book_id', '=', 'books.id')
            ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
            ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
            ->select(
                'genres.id as id',
                'genres.name as genre_name',
                DB::raw('COUNT(reviews.id) as count'),
                DB::raw('ROUND(AVG(reviews.rating), 1) as avg_rating')
            )
            ->groupBy('genres.id', 'genres.name')
            ->orderBy('avg_rating', 'desc')
            ->take(5)
            ->get();

        $stats = [
            'summary' => $summary,
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
