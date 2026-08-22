<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * マイ読書レポート一覧画面の表示
     */
    public function index(): View
    {
        $user = Auth::user();

        $reviews = Review::where('user_id', $user->id)
            ->with('book.genres')
            ->get();

        $summary = [
            'total_reviews' => $reviews->count(),
            'books_read' => $reviews->unique('book_id')->count(),
            'average_rating' => round($reviews->avg('rating') ?? 0.0, 1),
        ];

        $ratingDistribution = collect([1, 2, 3, 4, 5])
            ->mapWithKeys(fn ($rating) => [
                $rating => $reviews->where('rating', $rating)->count(),
            ]);

        $topRatedBooks = $reviews
            ->filter(fn ($review) => $review->rating >= 4)
            ->sortByDesc('created_at')
            ->sortByDesc('rating')
            ->take(5)
            ->map(fn ($review) => [
                'id' => $review->book->id ?? null,
                'title' => $review->book->title ?? '',
                'author' => $review->book->author ?? '',
                'rating' => $review->rating,
            ])
            ->values()
            ->all();

        $genreRatings = $reviews
            ->flatMap(fn ($review) => ($review->book->genres ?? collect())->map(fn ($genre) => [
                'genre_id' => $genre->id,
                'genre_name' => $genre->name,
                'rating' => $review->rating,
            ])
            )
            ->groupBy('genre_id')
            ->map(fn ($group) => [
                'id' => $group->first()['genre_id'],
                'name' => $group->first()['genre_name'],
                'count' => $group->count(),
                'average_rating' => round($group->avg('rating'), 1),
            ])
            ->sortByDesc('average_rating')
            ->take(5)
            ->values()
            ->all();

        $stats = [
            'summary' => $summary,
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
