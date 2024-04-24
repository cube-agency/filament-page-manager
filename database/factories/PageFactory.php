<?php

namespace CubeAgency\FilamentPageManager\Database\Factories;

use Carbon\Carbon;
use CubeAgency\FilamentPageManager\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $name = $this->faker->text(20);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'activate_at' => Carbon::now()->subMinute()
        ];
    }
}
