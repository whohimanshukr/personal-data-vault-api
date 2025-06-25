<?php

namespace Database\Factories;

use App\Models\DataCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonalData>
 */
class PersonalDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dataTypes = [
            'password' => [
                'titles' => ['Gmail Password', 'Facebook Login', 'GitHub Account', 'Netflix Password', 'Amazon Account'],
                'data' => ['SecurePass123!', 'MyFacebookPass456', 'GitHubSecure789', 'NetflixWatch2024', 'AmazonShop321']
            ],
            'note' => [
                'titles' => ['WiFi Password', 'Important Phone Number', 'Meeting Notes', 'Shopping List', 'Travel Plans'],
                'data' => ['HomeWiFi: MySecureNetwork2024', 'Emergency: +1-555-0123', 'Team meeting at 2 PM tomorrow', 'Milk, Bread, Eggs, Coffee', 'Flight to NYC on March 15th']
            ],
            'card' => [
                'titles' => ['Visa Credit Card', 'MasterCard', 'American Express', 'Debit Card', 'Business Card'],
                'data' => [
                    json_encode(['number' => '4111111111111111', 'expiry' => '12/25', 'cvv' => '123']),
                    json_encode(['number' => '5555555555554444', 'expiry' => '06/26', 'cvv' => '456']),
                    json_encode(['number' => '378282246310005', 'expiry' => '09/27', 'cvv' => '789']),
                    json_encode(['number' => '4000000000000002', 'expiry' => '03/26', 'cvv' => '321']),
                    json_encode(['number' => '5105105105105100', 'expiry' => '11/25', 'cvv' => '654'])
                ]
            ],
            'account' => [
                'titles' => ['Bank Account', 'Investment Account', 'PayPal Account', 'Venmo Account', 'Cryptocurrency Wallet'],
                'data' => ['Account: 1234567890, Routing: 021000021', 'Portfolio: Tech Stocks, Bonds, ETFs', 'PayPal: user@example.com', 'Venmo: @username', 'BTC: 1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa']
            ]
        ];

        $dataType = $this->faker->randomElement(array_keys($dataTypes));
        $typeData = $dataTypes[$dataType];
        $index = $this->faker->numberBetween(0, count($typeData['titles']) - 1);

        $tags = [
            'password' => [['email', 'google'], ['social', 'facebook'], ['development', 'github'], ['entertainment', 'netflix'], ['shopping', 'amazon']],
            'note' => [['wifi', 'network'], ['emergency', 'contact'], ['work', 'meeting'], ['shopping', 'grocery'], ['travel', 'vacation']],
            'card' => [['finance', 'credit'], ['finance', 'debit'], ['business', 'expense'], ['personal', 'shopping'], ['emergency', 'backup']],
            'account' => [['finance', 'banking'], ['investment', 'portfolio'], ['payment', 'online'], ['payment', 'mobile'], ['crypto', 'bitcoin']]
        ];

        return [
            'title' => $typeData['titles'][$index],
            'description' => $this->faker->sentence(),
            'data_type' => $dataType,
            'encrypted_data' => Crypt::encryptString($typeData['data'][$index]),
            'tags' => $tags[$dataType][$index] ?? [],
            'is_favorite' => $this->faker->boolean(20),
            'category_id' => DataCategory::factory(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the personal data is a password.
     */
    public function password(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type' => 'password',
            'title' => $this->faker->randomElement(['Gmail Password', 'Facebook Login', 'GitHub Account']),
            'encrypted_data' => Crypt::encryptString($this->faker->password()),
            'tags' => $this->faker->randomElement([['email', 'google'], ['social', 'facebook'], ['development', 'github']]),
        ]);
    }

    /**
     * Indicate that the personal data is a note.
     */
    public function note(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type' => 'note',
            'title' => $this->faker->randomElement(['WiFi Password', 'Important Phone Number', 'Meeting Notes']),
            'encrypted_data' => Crypt::encryptString($this->faker->sentence()),
            'tags' => $this->faker->randomElement([['wifi', 'network'], ['emergency', 'contact'], ['work', 'meeting']]),
        ]);
    }

    /**
     * Indicate that the personal data is a credit card.
     */
    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type' => 'card',
            'title' => $this->faker->randomElement(['Visa Credit Card', 'MasterCard', 'American Express']),
            'encrypted_data' => Crypt::encryptString(json_encode([
                'number' => $this->faker->creditCardNumber(),
                'expiry' => $this->faker->creditCardExpirationDateString(),
                'cvv' => $this->faker->numberBetween(100, 999)
            ])),
            'tags' => $this->faker->randomElement([['finance', 'credit'], ['finance', 'debit'], ['business', 'expense']]),
        ]);
    }

    /**
     * Indicate that the personal data is a favorite.
     */
    public function favorite(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_favorite' => true,
        ]);
    }
}
