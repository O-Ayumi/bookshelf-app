<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->isbn13(),
            'published_date' => $this->faker->date(),
            'description' => $this->faker->paragraph(),
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
