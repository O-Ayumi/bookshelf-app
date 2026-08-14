<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function __construct()
    {
        // create,store,edit,update,destroyだけログイン制限あり
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexBookRequest $request)
    {
        $genres = Genre::all();

        $validated = $request->validated();

        $query = Book::query();

        if (!empty($validated['keyword'])) {
            $keyword = $validated['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('author', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($validated['genre'])) {
            $genreId = $validated['genre'];
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('book_genre.genre_id', $genreId);
            });
        }

        $sort = $validated['sort'] ?? 'newest';

        $query->reorder();

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')
                    ->orderByRaw('reviews_avg_rating IS NULL ASC')
                    ->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
                break;
        }

        $books = $query->paginate(10)->appends($request->query());

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        try {
            $bookData = $request->validated();
            $bookData['user_id'] = auth()->id();

            $book = Book::create($bookData);

            if ($request->has('genres')) {
                $book->genres()->attach($request->genres);
            }

            return redirect()->route('books.show', ['book' => $book->id])->with('success', '書籍を登録しました');

        } catch (Exception $e) {
            Log::error('書籍登録失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '登録に失敗しました');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        try {
            $this->authorize('update', $book);

            $book->update($request->validated());
            if ($request->has('genres')) {
                $book->genres()->sync($request->genres);
            } else {
                $book->genres()->detach();
            }

            return redirect()->route('books.show', $book)->with('success', '書籍を更新しました');
        } catch (Exception $e) {
            Log::error('書籍更新失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '更新に失敗しました');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        try {
            $book->delete();

            return redirect()->route('books.index')->with('success', '書籍を削除しました');
        } catch (Exception $e) {
            Log::error('書籍削除失敗:' . $e->getMessage());

            return back()->with('error', '削除に失敗しました');
        }
    }

    public function fetchBookByIsbn(string $isbn)
    {
        $cleanIsbn = str_replace(['-', ' '], '', $isbn);

        try {
            $response = Http::timeout(5)
                ->get("https://www.googleapis.com/books/v1/volumes", [
                    'q' => 'isbn:' . $cleanIsbn,
                    'key' => env('GOOGLE_BOOKS_API_KEY'),
                ]);

            if ($response->failed()) {
                Log::error("ISBN検索API通信エラー：" . $response->status());
                return response()->json(['error' => 'API通信に失敗しました'], 500);
            }

            $data = $response->json();

            if (($data['totalItems'] ?? 0) === 0 || empty($data['items'])) {
                return response()->json(['error' => '書籍情報が見つかりませんでした'], 404);
            }

            $volumeInfo = $data['items'][0]['volumeInfo'] ?? [];

            // 配列の著者名をBladeが読み込めるよう文字列に変換
            $authors = $volumeInfo['authors'] ?? ['著者不明'];
            $authorString = implode(', ', $authors);

            // 画像URLの取得とhttps化
            $thumbnail = $volumeInfo['imageLinks']['thumbnail'] ?? '';
            if ($thumbnail) {
                $thumbnail = str_replace('http://', 'https://', $thumbnail);
            }

            return response()->json([
                'title' => $volumeInfo['title'] ?? null,
                'author' => $authorString,
                'description' => $volumeInfo['description'] ?? null,
                'published_date' => $volumeInfo['publishedDate'] ?? null,
                'image_url' => $thumbnail,
            ]);

        } catch (Exception $e) {
            Log::error("ISBN検索中に例外が発生しました:" . $e->getMessage());
            return response()->json(['error' => 'サーバーエラーが発生しました'], 500);
        }
    }
}
