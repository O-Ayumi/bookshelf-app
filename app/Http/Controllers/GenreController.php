<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\genre;
use Illuminate\Support\Facades\Log;

class GenreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $genres = genre::orderBy('id', 'asc')->paginate(10);

        return view('genres.index', compact('genres'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('genres.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGenreRequest $request)
    {
        try {
            genre::create($request->validated());

            return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました');
        } catch (Exception $e) {
            Log::error('ジャンル登録失敗:'.$e->getMessage());

            return back()->withInput()->with('error', '登録に失敗しました');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(genre $genre)
    {
        return view('genres.show', compact('genre'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGenreRequest $request, genre $genre)
    {
        try {
            $genre->update($request->validated());

            return redirect()->route('genres.index')->with('success', 'ジャンル名を更新しました');
        } catch (Exception $e) {
            Log::error('ジャンル更新失敗:'.$e->getMessage());

            return back()->withInput()->with('error', '更新に失敗しました');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(genre $genre)
    {
        try {
            $genre->delete();

            return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました');
        } catch (Exception $e) {
            Log::error('ジャンル削除失敗:'.$e->getMessage());

            return back()->with('error', '削除に失敗しました');
        }
    }
}
