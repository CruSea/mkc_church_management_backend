<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberSpouseInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_spouse_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned()->nullable();
            $table->string('full_name');
            $table->string('photo_url')->nullable();
            $table->string('city')->nullable();
            $table->string('phone_cell')->nullable();
            $table->string('phone_work')->nullable();
            $table->string('phone_home')->nullable();
            $table->string('email')->nullable();
            $table->string('birth_day')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employment_place')->nullable();
            $table->string('employment_position')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('salvation_date')->nullable();
            $table->string('is_baptized')->nullable();
            $table->string('address')->nullable();
            $table->string('baptized_date')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_spouse_infos');
    }
}
