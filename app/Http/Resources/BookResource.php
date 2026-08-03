<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_date,
            'description' => $this->description ?? '',
            'image_url' => $this->image_url ?? '',

            'average_rating' => $this->average_rating ? round($this->average_rating, 1) : 0,
            'reviews_count' => $this->reviews_count ?? 0,

            'genres' => $this->genres->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ];
            }),

            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'reviewr_name' => $review->reviewr_name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at?->toIso8601String(),
                    ];
                });
            }),

            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
