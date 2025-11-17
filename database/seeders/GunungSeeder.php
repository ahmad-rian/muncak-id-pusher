<?php

namespace Database\Seeders;

use App\Models\Gunung;
use App\Models\KabupatenKota;
use App\Models\Point;
use App\Models\Provinsi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GunungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlPath = database_path('seeders/sql/negara-20250815.sql');
        $sql     = File::get($sqlPath);
        DB::unprepared($sql);

        $sqlPath = database_path('seeders/sql/rute_tingkat_kesulitan-20250815.sql');
        $sql     = File::get($sqlPath);
        DB::unprepared($sql);

        $sqlPath = database_path('seeders/sql/gunung-20250815.sql');
        $sql     = File::get($sqlPath);
        DB::unprepared($sql);

        $sqlPath = database_path('seeders/sql/rute-20250815.sql');
        $sql     = File::get($sqlPath);
        DB::unprepared($sql);

        $sqlPath = database_path('seeders/sql/point-20250815.sql');
        $sql     = File::get($sqlPath);
        DB::unprepared($sql);
    }
}
