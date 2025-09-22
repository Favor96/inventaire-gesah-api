<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeImmobilisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('type_immobilisations')->insert([
            ['nom' => 'Immobilisations incorporelles'],
            ['nom' => 'Immobilisations corporelles'],
            ['nom' => 'Immobilisations financières'],
        ]);
    }
}
