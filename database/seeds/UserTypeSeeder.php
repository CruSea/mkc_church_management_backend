<?php

use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_type_1 = new \App\UserType();
        $user_type_1->name = "System Admin";
        $user_type_1->description = "System Admin";
        $user_type_1->save();

        $user_type_2 = new \App\UserType();
        $user_type_2->name = "Mobile User";
        $user_type_2->description = "Mobile User";
        $user_type_2->save();
    }
}
