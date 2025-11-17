<?php

namespace Database\Seeders;

use App\Http\Controllers\UserController;
use App\Models\Gunung;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(["name" => "admin"]);
        $user  = Role::create(["name" => "user"]);

        $controller = new UserController();

        $admin1 = User::create([
            "name"              => "Admin 1",
            "email"             => "admin@admin",
            "username"          => uniqid("admin_"),
            "password"          => Hash::make("123123123"),
            "email_verified_at" => now(),
        ]);
        $admin1->assignRole($admin);
        $controller->createPhotoProfile($admin1);

        $admin2 = User::create([
            "name"              => "Admin 2",
            "email"             => "admin@muncak.id",
            "username"          => uniqid("admin_"),
            "password"          => Hash::make("123123123"),
            "email_verified_at" => now(),
        ]);
        $admin2->assignRole($admin);
        $controller->createPhotoProfile($admin2);

        $user1 = User::create([
            "name"              => "User 1",
            "email"             => "user1@muncak.id",
            "username"          => uniqid("user"),
            "password"          => Hash::make("123123123"),
            "email_verified_at" => now(),
        ]);
        $user1->assignRole($user);
        $controller->createPhotoProfile($user1);
    }
}
