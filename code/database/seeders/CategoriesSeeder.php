<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['id_parent' => null, 'id' => 1, 'name' => 'Application'],
            ['id_parent' => null, 'id' => 2, 'name' => 'Game'],

            ['id_parent' => 2, 'id' => 3, 'name' => 'Sport'],
            ['id_parent' => 2, 'id' => 4, 'name' => 'Puzzle'],
            ['id_parent' => 2, 'id' => 5, 'name' => 'Simulation'],
            ['id_parent' => 2, 'id' => 23, 'name' => 'Action']
        ]);
    }
}
