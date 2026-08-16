<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     *書籍に紐づくユーザーを取得
     *
     * @return BelongsTo　ユーザーモデルとの一対多リレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 書籍に紐づくジャンルを取得
     *
     * @return BelongsToMany　ジャンルモデルとの多対多リレーション
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre', 'book_id', 'genre_id')->withTimestamps();
    }

    /**
     * 書籍に紐づくお気に入りユーザーを取得
     *
     * @return BelongsToMany　お気に入りモデルとの多対多リレーション
     */
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'book_id', 'user_id')->withTimestamps();
    }

    /**
     * 書籍に紐づくレビューを取得
     *
     * @return HasMany　レビューモデルとの一対多リレーション
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 書籍に紐づく読書計画を取得
     *
     * @return HasMany　読書計画モデルとの一対多リレーション
     */
    public function readingPlans(): HasMany
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
