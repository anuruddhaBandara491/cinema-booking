<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('languages')->insert([
            ['name' => 'Sinhala'],
            ['name' => 'English'],
            ['name' => 'Tamil'],
        ]);
    }
}
