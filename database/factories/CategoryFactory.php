<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        // No unique() here: the pool is small, so it overflows across a long
        // suite and the fallback can hand two factories the same slug.
        $name = fake()->randomElement(['Tas Ransel', 'Dompet', 'Tas Seldang', 'Koper', 'Tas Pinggang', 'Dompet Kartu']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
