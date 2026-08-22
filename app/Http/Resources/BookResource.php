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
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_date,
            'description' => $this->description ?? '',
            'image_url' => $this->image_url ?? '',

            'genres' => $this->genres->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ];
            }),

            'created_at' => $this->created_at->toIso8601String(),
        ];

        if ($request->routeIs('*.index') || isset($this->reviews_count)) {
            $data['average_rating'] = $this->average_rating ? round($this->average_rating, 1) : 0;
            $data['reviews_count'] = $this->reviews_count ?? 0;
        }

        if ($this->relationLoaded('reviews')) {
            $data['reviews'] = $this->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'reviewer_name' => $review->reviewer_name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at?->toIso8601String(),
                ];
            });
        }

        return $data;
    }
}
