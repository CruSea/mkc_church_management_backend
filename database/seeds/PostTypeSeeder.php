<?php

use Illuminate\Database\Seeder;

class PostTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $post_type_1 = new \App\PostType();
        $post_type_1->name = "Video Post";
        $post_type_1->description = "Video Post";
        $post_type_1->save();

        $post_type_2 = new \App\PostType();
        $post_type_2->name = "Image Post";
        $post_type_2->description = "Image Post";
        $post_type_2->save();

        $post_type_3 = new \App\PostType();
        $post_type_3->name = "Text Post";
        $post_type_3->description = "Text Post";
        $post_type_3->save();
    }
}
