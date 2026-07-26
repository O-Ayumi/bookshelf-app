<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'review_user', 'review_id', 'usre_id')->withTimestamps();
    }
}
