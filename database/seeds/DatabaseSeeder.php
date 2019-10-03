<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call(PostTypeSeeder::class);
         $this->call(UserTypeSeeder::class);
         $this->call(UserRoleSeeder::class);
         $this->call(TeamCategoriesSeeder::class);
    }
}
