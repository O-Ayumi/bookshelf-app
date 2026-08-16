<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * ジャンルに紐づく書籍を取得
     *
     * @return BelongsToMany　書籍モデルとの多対多リレーション
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_genre', 'genre_id', 'book_id')->withTimestamps();
    }
}
