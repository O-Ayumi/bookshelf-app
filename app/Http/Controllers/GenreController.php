<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class GenreController extends Controller
{
    /**
     * ジャンル一覧表示
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル作成画面の表示
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * 新規ジャンルの保存
     *
     * @param  StoreGenreRequest  $request  バリデーション済のリクエスト
     * @return RedirectResponse　ジャンル一覧へ遷移
     */
    public function store(StoreGenreRequest $request): RedirectResponse
    {
        try {
            Genre::create($request->validated());

            return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました');
        } catch (Exception $e) {
            Log::error('ジャンル登録失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '登録に失敗しました');
        }
    }

    /**
     * ジャンル詳細表示
     *
     * @param  Genre  $genre  特定のジャンル
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル編集画面の表示
     *
     * @param  Genre  $genre  特定のジャンル
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンル更新
     *
     * @param  UpdateGenreRequest  $request  バリデーション済のリクエスト
     * @param  Genre  $genre  特定のジャンル
     * @return RedirectResponse　ジャンル一覧へ遷移
     */
    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        try {
            $genre->update($request->validated());

            return redirect()->route('genres.index')->with('success', 'ジャンル名を更新しました');
        } catch (Exception $e) {
            Log::error('ジャンル更新失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '更新に失敗しました');
        }
    }

    /**
     * ジャンル削除
     *
     * @param  Genre  $genre  特定のジャンル
     * @return RedirectResponse　ジャンル一覧へ遷移
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        try {
            if ($genre->books()->exists()) {
                return back()->with('error', 'このジャンルには書籍が紐付いているため削除できません。');
            }

            $genre->delete();

            return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました');
        } catch (Exception $e) {
            Log::error('ジャンル削除失敗:' . $e->getMessage());

            return back()->with('error', '削除に失敗しました');
        }
    }
}
