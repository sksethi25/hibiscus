<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class FormFieldsTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
          DB::table('form_field_types')->truncate();

    	DB::table('form_field_types')->insert([
            'type' => 'text'
        ]);
        DB::table('form_field_types')->insert([
        	 'type' => 'checkbox'
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
