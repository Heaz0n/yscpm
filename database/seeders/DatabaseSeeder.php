<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
public function run()
{
    $this->call(SchoolsTableSeeder::class);
    $this->call(CategoriesTableSeeder::class);
    $this->call(GroupsTableSeeder::class);
    $this->call(UsersTableSeeder::class);
    $this->call(StudentsTableSeeder::class);
}
}
