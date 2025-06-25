<?php

namespace Database\Seeders;

use App\Models\DataCategory;
use App\Models\PersonalData;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class PersonalDataVaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo user
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Category templates
        $categoryTemplates = [
            [
                'name' => 'Passwords',
                'description' => 'Website and application passwords',
                'color' => '#EF4444',
                'icon' => 'lock',
            ],
            [
                'name' => 'Credit Cards',
                'description' => 'Credit and debit card information',
                'color' => '#10B981',
                'icon' => 'credit-card',
            ],
            [
                'name' => 'Bank Accounts',
                'description' => 'Bank account details and credentials',
                'color' => '#3B82F6',
                'icon' => 'bank',
            ],
            [
                'name' => 'Notes',
                'description' => 'Personal notes and important information',
                'color' => '#F59E0B',
                'icon' => 'file-text',
            ],
            [
                'name' => 'Social Media',
                'description' => 'Social media account credentials',
                'color' => '#8B5CF6',
                'icon' => 'users',
            ]
        ];

        // Create categories for the demo user
        $createdCategories = [];
        foreach ($categoryTemplates as $template) {
            $createdCategories[] = DataCategory::create(array_merge($template, ['user_id' => $user->id]));
        }

        // Create sample personal data
        $sampleData = [
            [
                'title' => 'Gmail Password',
                'description' => 'My Gmail account password',
                'data_type' => 'password',
                'encrypted_data' => Crypt::encryptString('SecureGmailPass123!'),
                'tags' => ['email', 'google'],
                'is_favorite' => true,
                'category_id' => $createdCategories[0]->id, // Passwords
                'user_id' => $user->id
            ],
            [
                'title' => 'Facebook Login',
                'description' => 'Facebook account credentials',
                'data_type' => 'password',
                'encrypted_data' => Crypt::encryptString('MyFacebookPass456'),
                'tags' => ['social', 'facebook'],
                'is_favorite' => false,
                'category_id' => $createdCategories[4]->id, // Social Media
                'user_id' => $user->id
            ],
            [
                'title' => 'Visa Credit Card',
                'description' => 'Primary credit card for online purchases',
                'data_type' => 'card',
                'encrypted_data' => Crypt::encryptString(json_encode([
                    'number' => '4111111111111111',
                    'expiry' => '12/25',
                    'cvv' => '123',
                    'name' => 'Demo User'
                ])),
                'tags' => ['finance', 'credit'],
                'is_favorite' => true,
                'category_id' => $createdCategories[1]->id, // Credit Cards
                'user_id' => $user->id
            ],
            [
                'title' => 'Bank Account Details',
                'description' => 'Primary checking account information',
                'data_type' => 'account',
                'encrypted_data' => Crypt::encryptString('Account: 1234567890, Routing: 021000021, Bank: Demo Bank'),
                'tags' => ['finance', 'banking'],
                'is_favorite' => true,
                'category_id' => $createdCategories[2]->id, // Bank Accounts
                'user_id' => $user->id
            ],
            [
                'title' => 'WiFi Password',
                'description' => 'Home WiFi network password',
                'data_type' => 'note',
                'encrypted_data' => Crypt::encryptString('Network: HomeWiFi, Password: MySecureNetwork2024'),
                'tags' => ['wifi', 'network'],
                'is_favorite' => false,
                'category_id' => $createdCategories[3]->id, // Notes
                'user_id' => $user->id
            ],
            [
                'title' => 'GitHub Account',
                'description' => 'GitHub development account',
                'data_type' => 'password',
                'encrypted_data' => Crypt::encryptString('GitHubSecure789'),
                'tags' => ['development', 'github'],
                'is_favorite' => false,
                'category_id' => $createdCategories[0]->id, // Passwords
                'user_id' => $user->id
            ],
            [
                'title' => 'Netflix Password',
                'description' => 'Netflix streaming account',
                'data_type' => 'password',
                'encrypted_data' => Crypt::encryptString('NetflixWatch2024'),
                'tags' => ['entertainment', 'netflix'],
                'is_favorite' => false,
                'category_id' => $createdCategories[0]->id, // Passwords
                'user_id' => $user->id
            ],
            [
                'title' => 'Important Phone Numbers',
                'description' => 'Emergency and important contact numbers',
                'data_type' => 'note',
                'encrypted_data' => Crypt::encryptString('Emergency: 911, Doctor: +1-555-0123, Work: +1-555-0456'),
                'tags' => ['emergency', 'contact'],
                'is_favorite' => true,
                'category_id' => $createdCategories[3]->id, // Notes
                'user_id' => $user->id
            ]
        ];

        foreach ($sampleData as $data) {
            PersonalData::create($data);
        }

        // Create additional random data using factories
        PersonalData::factory(20)
            ->for($user)
            ->create();

        // Create additional users with their own unique categories and data
        User::factory(3)->create()->each(function ($user) use ($categoryTemplates) {
            // Shuffle and assign unique categories to this user
            $shuffled = collect($categoryTemplates)->shuffle()->take(3)->all();
            $userCategories = [];
            foreach ($shuffled as $template) {
                $userCategories[] = DataCategory::create(array_merge($template, ['user_id' => $user->id]));
            }
            // Create personal data for each user
            PersonalData::factory(10)
                ->for($user)
                ->create();
        });

        $this->command->info('Personal Data Vault seeded successfully!');
        $this->command->info('Demo user: demo@example.com / password123');
    }
} 