<?php

namespace Database\Seeders;

use App\Models\Suggestion;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\SuggestionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::where('email', 'info@snappysnail.io')->exists()) {
            User::factory()->create([
                'name' => 'gE',
                'email' => 'info@snappysnail.io',
                'password' => bcrypt(env('SSNAIL_ADMIN_PWD')),
            ]);
        }

        $this->call(SuggestionsSeeder::class);
    }
}
