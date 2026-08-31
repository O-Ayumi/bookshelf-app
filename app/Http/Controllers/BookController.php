<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * 書籍一覧と検索機能
     *
     * @param  IndexBookRequest  $request  バリデーション済のリクエスト
     * @return View 一覧画面と絞り込み表示、20件ごとのページネーション
     */
    public function index(IndexBookRequest $request): View
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
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
                break;
        }

        $books = $query->paginate(10)->appends($request->query());

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録画面を表示
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍情報を登録する
     *
     * @param  StoreBookRequest  $request  バリデーション済のリクエスト
     * @return RedirectResponse 詳細画面へリダイレクト、エラー時の戻り遷移
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        try {
            $bookData = $request->validated();
            $bookData['user_id'] = auth()->id();

            $book = DB::transaction(function () use ($bookData, $request) {
                $createdBook = Book::create($bookData);

                if ($request->has('genres')) {
                    $createdBook->genres()->attach($request->genres);
                }

                return $createdBook;
            });

            return redirect()->route('books.show', ['book' => $book->id])->with('success', '書籍を登録しました');

        } catch (Exception $e) {
            Log::error('書籍登録失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '登録に失敗しました');
        }
    }

    /**
     * 書籍情報の詳細表示
     *
     * @param  Book  $book  特定の書籍
     * @return View 書籍詳細情報の表示
     */
    public function show(Book $book): View
    {
        $book->load(['genres', 'reviews.user']);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集
     *
     * @param  Book  $book  特定の書籍
     * @return View 書籍編集画面の表示
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍情報の更新
     *
     * @param  UpdateBookRequest  $request,  Book $book バリデーション済の特定の書籍情報
     * @return RedirectResponse 書籍詳細画面へ遷移、エラー時の戻り遷移
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        try {
            DB::transaction(function () use ($request, $book) {
                $book->update($request->only(['title', 'author']));
                $book->genres()->sync($request->input('genres'));
            });

            return redirect()->route('books.show', $book)->with('success', '書籍を更新しました');
        } catch (Exception $e) {
            Log::error('書籍更新失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '更新に失敗しました');
        }
    }

    /**
     * 書籍の削除
     *
     * @param  Book  $book  特定の書籍情報
     * @return RedirectResponse 削除後一覧へ遷移、エラー時の戻り遷移
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);
        try {
            DB::transaction(function () use ($book) {
                $book->genres()->detach();
                $book->delete();
            });

            return redirect()->route('books.index')->with('success', '書籍を削除しました');
        } catch (Exception $e) {
            Log::error('書籍削除失敗:' . $e->getMessage());

            return back()->with('error', '削除に失敗しました');
        }
    }

    /**
     * Google Books APIを利用してISBNから書籍情報を取得
     *
     * @param  string  $isbn  ハイフンやスペースを含む可能性のあるISBNコード
     * @return JsonResponse　書籍データまたはエラーメッセージのJSONレスポンス
     */
    public function fetchBookByIsbn(Request $request, string $isbn): JsonResponse
    {
        $cleanIsbn = str_replace(['-', ' '], '', $isbn);

        try {
            $response = Http::timeout(5)
                ->get('https://www.googleapis.com/books/v1/volumes', [
                    'q' => 'isbn:' . $cleanIsbn,
                    'key' => config('services.google.books_api_key'),
                ]);

            if ($response->failed()) {
                Log::error('ISBN検索API通信エラー：' . $response->status());

                return response()->json(['error' => 'API通信に失敗しました'], 500);
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::error('Google Books API内部エラー：' . json_encode($data['error']));

                return response()->json(['error' => 'API内部でエラーが発生しました'], 500);
            }

            if (($data['totalItems'] ?? 0) === 0 || empty($data['items'])) {
                return response()->json(['error' => '書籍情報が見つかりませんでした'], 404);
            }

            $volumeInfo = $data['items'][0]['volumeInfo'] ?? [];

            // 配列の著者名をレスポンス用に文字列に変換
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
            Log::error('ISBN検索中に例外が発生しました:' . $e->getMessage());

            return response()->json(['error' => 'サーバーエラーが発生しました'], 500);
        }
    }
}
