<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Operasional\TargetKontribusi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TargetKontribusiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TargetKontribusi::insert([
            ['kode' => 'DISC_MANUAL', 'nama' => 'Disc Manual', 'persen' => 2.5],
            ['kode' => 'RETUR',       'nama' => 'Retur',       'persen' => 1.0],
            ['kode' => 'GAS',         'nama' => 'Gas',         'persen' => 3.0],
            ['kode' => 'TELUR',       'nama' => 'Telur',       'persen' => 5.0],
        ]);
    }
}
