<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()

    {
        Schema::disableForeignKeyConstraints();
        DB::table('role')->truncate();

    	DB::table('role')->insert([
        	'id'=>0,
            'name' => 'Admin',
            'type' => 'admin'
        ]);
        DB::table('role')->insert([
        	'id'=>1,
            'name' => 'Patient',
            'type' => 'patient'
        ]);
        DB::table('role')->insert([
        	'id'=>2,
            'name' => 'Doctor',
            'type' => 'doctor'
        ]);
        DB::table('role')->insert([
            'id'=>3,
            'name' => 'Staff',
            'type' => 'staff'
        ]);
        DB::table('role')->insert([
            'id'=>4,
            'name' => 'DME',
            'type' => 'dme'
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
