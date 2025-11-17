<?php

namespace Database\Seeders\Prod;

use App\Models\Gunung;
use App\Models\Negara;
use App\Models\Provinsi;
use App\Models\Rute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModifyProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=Database\\Seeders\\Prod\\ModifyProvinsiSeeder
     */
    public function run(): void
    {
        $indonesia = Negara::firstOrCreate(
            ['slug' => 'indonesia'],
            [
                'nama'      => 'Indonesia',
                'slug'      => 'indonesia',
                'nama_lain' => 'Republic of Indonesia',
                'kode'      => 'ID'
            ]
        );

        Provinsi::whereNull('negara_id')->update([
            'negara_id' => $indonesia->id
        ]);

        Gunung::whereNull('negara_id')->update([
            'negara_id' => $indonesia->id
        ]);

        Rute::whereNull('negara_id')->update([
            'negara_id' => $indonesia->id
        ]);

        $this->command->info('Successfully created/updated Indonesia country and linked provinces.');
    }
}
