<?php

namespace Database\Seeders;

use App\Models\Administrateur;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run()
    {
        // Création de l'utilisateur avec le rôle admin
        $user = User::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_verified' => true,
            'role' => 'admin',
            'verification_code' => null,
        ]);

        // Création de l'administrateur lié à cet utilisateur
        Administrateur::create([
            'user_id' => $user->id,
            'nom' => 'Admin',
            'prenom' => 'User',
            'email' => $user->email,
        ]);
    }
}
