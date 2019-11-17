<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_id')->unsigned()->nullable();
            $table->integer('member_id')->unsigned()->nullable();
            $table->boolean("is_leader")->default(false);
            $table->boolean("is_main_leader")->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['team_id', 'member_id']);
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('CASCADE');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('CASCADE');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_members');
    }
}
