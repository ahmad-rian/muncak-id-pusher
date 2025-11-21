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
        $admin = Role::firstOrCreate(["name" => "admin"]);
        $user  = Role::firstOrCreate(["name" => "user"]);

        $controller = new UserController();

        $admin1 = User::firstOrCreate([
            "email"             => "admin@admin",
        ], [
            "name"              => "Admin 1",
            "username"          => uniqid("admin_"),
            "password"          => Hash::make("123123123"),
            "email_verified_at" => now(),
        ]);
        if (!$admin1->hasRole($admin->name)) {
            $admin1->assignRole($admin);
        }
        if (!$admin1->getFirstMedia('photo-profile')) {
            $controller->createPhotoProfile($admin1);
        }

        $admin2 = User::firstOrCreate([
            "email"             => "admin@muncak.id",
        ], [
            "name"              => "Admin 2",
            "username"          => uniqid("admin_"),
            "password"          => Hash::make("123123123"),
            "email_verified_at" => now(),
        ]);
        if (!$admin2->hasRole($admin->name)) {
            $admin2->assignRole($admin);
        }
        if (!$admin2->getFirstMedia('photo-profile')) {
            $controller->createPhotoProfile($admin2);
        }

        $user1 = User::firstOrCreate([
            "email"             => "user1@muncak.id",
        ], [
            "name"              => "User 1",
            "username"          => uniqid("user"),
            "password"          => Hash::make("123123123"),
            "email_verified_at" => now(),
        ]);
        if (!$user1->hasRole($user->name)) {
            $user1->assignRole($user);
        }
        if (!$user1->getFirstMedia('photo-profile')) {
            $controller->createPhotoProfile($user1);
        }
    }
}
