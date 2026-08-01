<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Exception;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with(['genres', 'user'])->oldest()->paginate(10);

        return view('books.index', compact('books'));
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
            Log::error('書籍登録失敗:'.$e->getMessage());

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
            Log::error('書籍更新失敗:'.$e->getMessage());

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
            Log::error('書籍削除失敗:'.$e->getMessage());

            return back()->with('error', '削除に失敗しました');
        }
    }
}
