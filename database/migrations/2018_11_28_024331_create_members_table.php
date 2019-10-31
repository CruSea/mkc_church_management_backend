<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('member_id')->unique();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('full_name');
            $table->string('photo_url')->nullable();
            $table->string('city')->nullable();
            $table->string('sub_city')->nullable();
            $table->string('wereda')->nullable();
            $table->string('house_number')->nullable();
            $table->string('church_group_place')->nullable();
            $table->string('phone_cell')->nullable();
            $table->string('phone_work')->nullable();
            $table->string('phone_home')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('birth_day')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('occupation')->nullable();
            $table->string('education_level')->nullable();
            $table->string('employment_position')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('address')->nullable();
            $table->string('salvation_date')->nullable();
            $table->string('salvation_church')->nullable();
            $table->string('is_baptized')->nullable();
            $table->string('baptized_date')->nullable();
            $table->string('baptized_church')->nullable();
            $table->string('marital_status')->nullable();
            $table->boolean('have_family_fellowship')->default(false);
            $table->text('emergency_contact_name')->nullable();
            $table->text('emergency_contact_phone')->nullable();
            $table->text('emergency_contact_subcity')->nullable();
            $table->text('emergency_contact_wereda')->nullable();
            $table->text('emergency_contact_house_no')->nullable();
            $table->text('remark')->nullable();
            $table-> boolean('status')->default(true);

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
}
