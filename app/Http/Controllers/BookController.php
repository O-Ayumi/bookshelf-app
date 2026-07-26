<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Support\Facades\Log;
use Exception;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with(['genres', 'user'])->latest()->paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('books.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        try {
            $book = Book::create($request->validated());

            return redirect()->route('books.show')->with('success', '書籍を登録しました');
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
        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        return view('books.edit', compact('book'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        try {
            $book->update($request->validated());

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
        try {
            $book->delete();

            return redirect()->route('books.index')->with('success', '書籍を削除しました');
        } catch (Exception $e) {
            Log::error('書籍削除失敗:' . $e->getMessage());

            return back()->with('error', '削除に失敗しました');
        }
    }
}