<?php

use Illuminate\Database\Seeder;

class TeamCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $team_category_1 = new \App\TeamCategory();
        $team_category_1->name = "Choir";
        $team_category_1->description = "Choir";
        $team_category_1->save();

        $team_category_2 = new \App\TeamCategory();
        $team_category_2->name = "Prayer";
        $team_category_2->description = "Prayer";
        $team_category_2->save();

        $team_category_3 = new \App\TeamCategory();
        $team_category_3->name = "Administration";
        $team_category_3->description = "Administration";
        $team_category_3->save();

        $team_category_3 = new \App\TeamCategory();
        $team_category_3->name = "Youth";
        $team_category_3->description = "Youth";
        $team_category_3->save();
    }
}
