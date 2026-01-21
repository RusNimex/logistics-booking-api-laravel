<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlotsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];

        for ($i = 0; $i < 10; $i++) {
            $capacity = random_int(1, 20);
            $rows[] = [
                'capacity' => $capacity,
                'remaining' => random_int(0, $capacity),
            ];
        }

        DB::table('slots')->insert($rows);
    }
}
