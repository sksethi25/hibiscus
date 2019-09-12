<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FormPatientsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         //creating role table
         Schema::create('form_patients_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('form_patients_id');
            $table->bigInteger('form_fields_id');
            $table->string('data');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
