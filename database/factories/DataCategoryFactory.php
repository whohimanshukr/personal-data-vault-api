<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataCategory>
 */
class DataCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            [
                'name' => 'Passwords',
                'description' => 'Website and application passwords',
                'color' => '#EF4444',
                'icon' => 'lock'
            ],
            [
                'name' => 'Credit Cards',
                'description' => 'Credit and debit card information',
                'color' => '#10B981',
                'icon' => 'credit-card'
            ],
            [
                'name' => 'Bank Accounts',
                'description' => 'Bank account details and credentials',
                'color' => '#3B82F6',
                'icon' => 'bank'
            ],
            [
                'name' => 'Notes',
                'description' => 'Personal notes and important information',
                'color' => '#F59E0B',
                'icon' => 'file-text'
            ],
            [
                'name' => 'Social Media',
                'description' => 'Social media account credentials',
                'color' => '#8B5CF6',
                'icon' => 'users'
            ],
            [
                'name' => 'Work',
                'description' => 'Work-related accounts and information',
                'color' => '#06B6D4',
                'icon' => 'briefcase'
            ],
            [
                'name' => 'Shopping',
                'description' => 'Online shopping accounts and payment methods',
                'color' => '#EC4899',
                'icon' => 'shopping-cart'
            ],
            [
                'name' => 'Health',
                'description' => 'Health insurance and medical information',
                'color' => '#84CC16',
                'icon' => 'heart'
            ]
        ];

        $category = $this->faker->randomElement($categories);

        return [
            'name' => $category['name'],
            'description' => $category['description'],
            'color' => $category['color'],
            'icon' => $category['icon'],
            'user_id' => User::factory(),
        ];
    }
}
