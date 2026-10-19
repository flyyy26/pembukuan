<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkSetoran extends Model
{
    // Tambahkan baris ini agar Laravel tidak menebak nama tabelnya jadi 'brilink_setorans'
    protected $table = 'brilink_setoran';

    protected $fillable = [
        'brilink_report_id',
        'nominal_edc',
        'administrasi_toko',
        'jumlah_setoran_riil',
    ];

    protected $casts = [
        'nominal_edc'         => 'integer',
        'administrasi_toko'   => 'integer',
        'jumlah_setoran_riil' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(BrilinkReport::class, 'brilink_report_id');
    }
}