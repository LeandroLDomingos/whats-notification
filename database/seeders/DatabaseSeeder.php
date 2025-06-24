<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Leandro Lucas Domingos',
            'email' => 'admin@admin.com',
            'phone' => '5531971841063',
        ]);

        Contact::factory()->create([
            'name' => 'Leandro Lucas Domingos',
            'phone' => '5531971841063',
        ]);
    }
}
