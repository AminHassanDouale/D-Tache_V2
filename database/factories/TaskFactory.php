<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status_id' => 1,
            'user_id' => 1,
            'assignee_id' => 2,
            'priority' => $this->faker->randomElement([1, 2, 3, 4]),
            'start_date' => now(),
            'tags' => json_encode($this->faker->words(5)),
            'end_date' => now()->addDays(10),
            'department_id' => 1,

        ];
    }
}
