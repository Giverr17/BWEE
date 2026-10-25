<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'misibor@yorku.ca'],
            ['name' => 'Dr. Mary Uhunoma Isibor', 'password' => Hash::make('ChangeThisImmediately!123')]
        );

        User::updateOrCreate(
            ['email' => 'adjoufan@yorku.ca'],
            ['name' => 'Dr. Georgette Arielle Djoufan Fotsing', 'password' => Hash::make('ChangeThisImmediately!123')]
        );
    }
}
