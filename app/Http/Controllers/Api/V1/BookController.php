<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 書籍一覧画面を取得
     *
     * @param  IndexBookRequest  $request  検索・絞り込み条件を含むリクエスト
     * @return AnonymousResourceCollection　書籍データのコレクションレスポンス
     */
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $query = Book::with(['genres'])->withAvg('reviews', 'rating')->withCount('reviews');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")->orWhere('author', 'like', "%{$keyword}%")->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre_id);
            });
        }

        $perPage = $request->input('per_page', 20);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $books = $query->latest()->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * 新しい書籍情報を登録する
     *
     * @param  StoreBookRequest  $request  バリデーション済のリクエスト
     * @return JsonResponse　ステータスコード201を含む登録成功レスポンス
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated) {
            $book = Book::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'author' => $validated['author'],
                'isbn' => $validated['isbn'],
                'published_date' => $validated['published_date'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
            ]);

            $book->genres()->sync($validated['genres'] ?? []);

            return $book;
        });

        $book->load(['genres', 'reviews.user']);
        $book->loadCount('reviews');
        $book->loadAvg('reviews', 'rating');

        return (new BookResource($book))->response()->setStatusCode(201);
    }

    /**
     * 指定した書籍の詳細画面
     *
     * @param  Book  $book  特定の書籍
     * @return BookResource　書籍詳細レスポンス
     */
    public function show(Book $book): BookResource
    {
        $book->load(['genres', 'reviews.user']);
        $book->loadCount('reviews');
        $book->loadAvg('reviews', 'rating');

        return new BookResource($book);
    }

    /**
     * 書籍情報の更新
     *
     * @param  UpdateBookRequest  $request  バリデーション済のリクエスト
     * @param  $id  特定の書籍
     * @return BookResource　更新後の書籍詳細
     */
    public function update(UpdateBookRequest $request, $id): BookResource
    {
        $book = Book::findOrFail($id);

        $this->authorize('update', $book);

        $validated = $request->validated();

        DB::transaction(function () use ($book, $validated) {
            $book->update([
                'user_id' => $book->user_id,
                'title' => $validated['title'],
                'author' => $validated['author'],
                'isbn' => $validated['isbn'],
                'published_date' => $validated['published_date'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
            ]);

            $book->genres()->sync($validated['genres'] ?? []);
        });

        $book->load(['genres', 'reviews.user']);
        $book->loadCount('reviews');
        $book->loadAvg('reviews', 'rating');

        return new BookResource($book);
    }

    /**
     * 書籍情報の削除
     *
     * @param  Request  $request  削除要求リクエスト
     * @param  Book  $book  特定の書籍情報
     * @return JsonResponse　ステータスコード204を含む削除成功レスポンス
     */
    public function destroy(Request $request, Book $book): JsonResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
