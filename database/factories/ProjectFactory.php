<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status_id' => $this->faker->numberBetween(1, 3), // Assuming you have status IDs from 1 to 10
            'priority' => $this->faker->randomElement([1, 2, 3, 4]),
            'privacy' => $this->faker->boolean,
            'start_date' => $this->faker->date,
            'due_date' => $this->faker->date,
            'remark' => $this->faker->text,
            'tags' => json_encode($this->faker->words(5)),
            'user_id' => $this->faker->numberBetween(1, 100), // Assuming you have user IDs from 1 to 10
            'department_id' => $this->faker->numberBetween(1, 5), // Assuming you have department IDs from 1 to 5
        ];
    }
}
