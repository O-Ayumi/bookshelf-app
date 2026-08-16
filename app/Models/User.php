<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * ユーザーに紐づくお気に入りを取得
     *
     * @return BelongsToMany　お気に入りモデルとの多対多リレーション
     */
    public function favoriteBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'favorites', 'user_id', 'book_id');
    }

    /**
     * ユーザーに紐づくいいねを取得
     *
     * @return BelongsToMany　いいねモデルとの多対多リレーション
     */
    public function likedReviews(): BelongsToMany
    {
        return $this->belongsToMany(Review::class, 'review_likes', 'user_id', 'review_id');
    }

    /**
     * ユーザーに紐づくレビューを取得
     *
     * @return HasMany　レビューモデルとの一対多リレーション
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * ユーザーに紐づく書籍を取得
     *
     * @return HasMany　書籍モデルとの一対多リレーション
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * ユーザーに紐づく読書計画を取得
     *
     * @return HasMany　読書計画モデルとの一対多リレーション
     */
    public function readingPlans(): HasMany
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
