<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'target_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'status' => ReadingPlanStatus::class,
        'target_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * 読書計画に紐づく書籍を取得
     *
     * @return BelongsTo　書籍モデルとの多対多リレーション
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * 読書計画に紐づくユーザーを取得
     *
     * @return BelongsTo　ユーザーモデルとの一対多リレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
