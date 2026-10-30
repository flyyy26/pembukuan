<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrilinkReport extends Model
{
    protected $fillable = [
        'store_id',
        'import_id',
        'file_name',
        'file_hash',
        'periode_start',
        'periode_end',
        'total_omset_ppob',
        'total_tarik_tunai_edc',
        'total_tarik_tunai_mbanking',
        'total_setoran_brilink',
        'total_pendapatan_admin',
        'total_pengeluaran_riil',
        'total_pindah_saldo',
        'volume_transaksi_agen',
        'saldo_awal_total',
        'saldo_akhir_total',
        'status',
        'error_message',
        'validasi_kas_uang',
        'manual_validasi_kas_uang',
        'manual_validasi_mutasi_rek',
        'manual_validasi_mutasi_fisik',
        'laba_kasir_pintar',
        'penambahan_brangkas',
        'manual_validasi_kas_uang',
        'manual_kas_akhir',
        'manual_kas_awal', 
    ];

    protected $casts = [
        'periode_start'              => 'date',
        'periode_end'                => 'date',
        'total_omset_ppob'           => 'integer',
        'total_tarik_tunai_edc'      => 'integer',
        'total_tarik_tunai_mbanking' => 'integer',
        'total_setoran_brilink'      => 'integer',
        'total_pendapatan_admin'     => 'integer',
        'total_pengeluaran_riil'     => 'integer',
        'total_pindah_saldo'         => 'integer',
        'volume_transaksi_agen'      => 'integer',
        'saldo_awal_total'           => 'integer',
        'saldo_akhir_total'          => 'integer',
        'validasi_kas_uang' => 'integer',
        'manual_validasi_kas_uang' => 'integer',
        'manual_validasi_mutasi_rek' => 'integer',
        'manual_validasi_mutasi_fisik' => 'integer',
        'laba_kasir_pintar' => 'integer',
        'penambahan_brangkas' => 'integer',
        'manual_kas_akhir' => 'integer',
        'manual_kas_awal' => 'integer',
    ];

    public function store()             { return $this->belongsTo(Store::class); }
    public function import()            { return $this->belongsTo(Import::class); }
    public function accounts()          { return $this->hasMany(BrilinkAccount::class); }
    public function ppobItems()         { return $this->hasMany(BrilinkPpobTransaction::class); }
    public function setoranItems()      { return $this->hasMany(BrilinkSetoran::class); }
    public function tarikTunaiItems()   { return $this->hasMany(BrilinkTarikTunai::class); }
    public function pengeluaranItems()  { return $this->hasMany(BrilinkPengeluaran::class); }

    /**
     * Profit bersih = Pendapatan Admin - Pengeluaran Riil.
     * (Pindah Saldo Internal TIDAK dihitung sebagai biaya.)
     */
    public function getProfitAttribute(): int
    {
        return $this->total_pendapatan_admin - $this->total_pengeluaran_riil;
    }

    /**
     * Total pendapatan admin gabungan (setoran + tarik tunai EDC + MBanking).
     * Berguna kalau nilai di dashboard tidak lengkap.
     */
    public function getPendapatanAdminCalcAttribute(): int
    {
        $setoranAdmin = $this->setoranItems->sum('administrasi_toko');
        $tarikAdmin   = $this->tarikTunaiItems->sum('admin_toko');
        $kmc          = $this->tarikTunaiItems->sum('kmc');

        return $setoranAdmin + $tarikAdmin + $kmc;
    }

    /**
     * Label periode untuk ditampilkan.
     */
    public function getPeriodeLabelAttribute(): string
    {
        if (!$this->periode_start) {
            return 'Tanpa periode';
        }
        return $this->periode_start->format('d/m/Y')
            . ($this->periode_end && $this->periode_end != $this->periode_start
                ? ' – ' . $this->periode_end->format('d/m/Y')
                : '');
    }

    public function saldoPindahItems()
    {
        return $this->hasMany(BrilinkSaldoPindah::class, 'brilink_report_id');
    }
}