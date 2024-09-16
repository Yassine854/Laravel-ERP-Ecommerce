<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Yassine',
            'email' => 'yassine@gmail.com',
            'role' => '0',
            'password' => Hash::make('#Yassine1')
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Sami',
            'email' => 'sami@gmail.com',
            'role' => '1',
            'password' => Hash::make('sami@gmail.com')
        ]);
    }
}
