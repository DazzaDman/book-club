<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'user_id' => User::factory(),
            'is_owner' => false,
        ];
    }

    /**
     * Indicate that the membership is the club's owner.
     */
    public function owner(): static
    {
        return $this->state(['is_owner' => true]);
    }
}
