<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    /**
     * レビューに紐づく書籍を取得
     *
     * @return BelongsTo　書籍モデルとの一対多リレーション
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * レビューに紐づくユーザーを取得
     *
     * @return BelongsTo　ユーザーモデルとの一対多リレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * レビューに紐づくいいねを取得
     *
     * @return BelongsToMany　いいねモデルとの多対多リレーション
     */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_likes', 'review_id', 'user_id')->withTimestamps();
    }
}
