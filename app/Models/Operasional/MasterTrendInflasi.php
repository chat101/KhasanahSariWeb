<?php

namespace App\Models\Operasional;

use Illuminate\Database\Eloquent\Model;

class MasterTrendInflasi extends Model
{
    protected $table = 'master_trend_inflasis';

    protected $fillable = [
        'tahun', 'bulan', 'trend', 'inflasi',
    ];

    public const BULAN_MAP = [
        1=>'JANUARI', 2=>'FEBRUARI', 3=>'MARET', 4=>'APRIL',
        5=>'MEI', 6=>'JUNI', 7=>'JULI', 8=>'AGUSTUS',
        9=>'SEPTEMBER', 10=>'OKTOBER', 11=>'NOPEMBER', 12=>'DESEMBER',
    ];

    public function getNamaBulanAttribute(): string
    {
        return self::BULAN_MAP[$this->bulan] ?? (string) $this->bulan;
    }
}
