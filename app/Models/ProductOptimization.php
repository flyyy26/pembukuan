<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOptimization extends Model
{
    protected $fillable = [
        'store_id', 'nama_produk', 'marketplace_sku',
        'harga_produk', 'biaya_layanan', 'ongkir',
        'diskon_produk', 'total_diskon_pengiriman', 'voucher_diskon', 'harga_final',
        // audit
        'audit_ukuran', 'audit_deskripsi', 'audit_stok', 'audit_harga', 'audit_rating',
        'audit_retur', 'audit_ongkir', 'audit_foto', 'audit_video',
        // ✅ solusi & status pelaksanaan
        'solusi_ukuran', 'solusi_deskripsi', 'solusi_stok', 'solusi_harga', 'solusi_rating',
        'solusi_retur', 'solusi_ongkir', 'solusi_foto', 'solusi_video',
        'sudah_ukuran', 'sudah_deskripsi', 'sudah_stok', 'sudah_harga', 'sudah_rating',
        'sudah_retur', 'sudah_ongkir', 'sudah_foto', 'sudah_video',
        'skor_optimasi', 'status_global', 'terakhir_audit',
    ];

    protected $casts = [
        'harga_produk'             => 'integer',
        'biaya_layanan'            => 'integer',
        'ongkir'                   => 'integer',
        'diskon_produk'            => 'integer',
        'total_diskon_pengiriman'  => 'integer',
        'voucher_diskon'           => 'integer',
        'harga_final'              => 'integer',
        'audit_ukuran'             => 'array',
        'audit_deskripsi'          => 'array',
        'audit_stok'               => 'array',
        'audit_harga'              => 'array',
        'audit_rating'             => 'array',
        'audit_retur'              => 'array',
        'audit_ongkir'             => 'array',
        'audit_foto'               => 'array',
        'audit_video'              => 'array',
        'skor_optimasi'            => 'integer',
        'terakhir_audit'           => 'date',
        'sudah_ukuran'    => 'boolean',
        'sudah_deskripsi' => 'boolean',
        'sudah_stok'      => 'boolean',
        'sudah_harga'     => 'boolean',
        'sudah_rating'    => 'boolean',
        'sudah_retur'     => 'boolean',
        'sudah_ongkir'    => 'boolean',
        'sudah_foto'      => 'boolean',
        'sudah_video'     => 'boolean',
    ];

    /**
     * Definisi aspek audit (label + icon).
     */
    public const ASPEK_AUDIT = [
        'ukuran'    => ['label' => 'Informasi Ukuran',         'icon' => 'mdi:ruler-square'],
        'deskripsi' => ['label' => 'Deskripsi',                'icon' => 'mdi:text-box-outline'],
        'stok'      => ['label' => 'Pilihan Ukuran & Stok',    'icon' => 'mdi:package-variant-closed'],
        'harga'     => ['label' => 'Harga',                    'icon' => 'mdi:tag'],
        'rating'    => ['label' => 'Rating & Ulasan',          'icon' => 'mdi:star-outline'],
        'retur'     => ['label' => 'Kebijakan Retur',          'icon' => 'mdi:keyboard-return'],
        'ongkir'    => ['label' => 'Ongkir',                   'icon' => 'mdi:truck-delivery-outline'],
        'foto'      => ['label' => 'Foto',                     'icon' => 'mdi:image-outline'],
        'video'     => ['label' => 'Video',                    'icon' => 'mdi:video-outline'],
    ];

    public const STATUS_META = [
        'aman'      => ['label' => 'Aman',              'color' => 'green',  'icon' => 'mdi:check-circle'],
        'evaluasi'  => ['label' => 'Perlu Evaluasi',    'color' => 'yellow', 'icon' => 'mdi:alert-circle-outline'],
        'perhatian' => ['label' => 'Perlu Perhatian',   'color' => 'red',    'icon' => 'mdi:alert-octagon'],
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Ambil nilai status aspek tertentu.
     */
    public function getAspek(string $key): array
    {
        $field = 'audit_' . $key;
        $val = $this->{$field} ?? [];
        return [
            'status' => $val['status'] ?? 'aman',
            'note'   => $val['note'] ?? '',
        ];
    }

    /**
     * Hitung skor optimasi 0-100 dari semua aspek.
     * aman=100, evaluasi=50, perhatian=0. Rata-rata.
     */
    public function hitungSkor(): int
    {
        $bobot = ['aman' => 100, 'evaluasi' => 50, 'perhatian' => 0];
        $total = 0;
        $count = 0;

        foreach (array_keys(self::ASPEK_AUDIT) as $key) {
            $aspek = $this->getAspek($key);
            // Skip aspek yang belum diisi (status null + note kosong)
            if (empty($aspek['note']) && ($this->{'audit_' . $key} === null)) {
                continue;
            }
            $total += $bobot[$aspek['status']] ?? 100;
            $count++;
        }

        return $count > 0 ? (int) round($total / $count) : 0;
    }

    /**
     * Tentukan status global: perhatian > evaluasi > aman.
     */
    public function hitungStatusGlobal(): string
    {
        $statuses = [];
        foreach (array_keys(self::ASPEK_AUDIT) as $key) {
            $aspek = $this->getAspek($key);
            if (!empty($aspek['note']) || $this->{'audit_' . $key} !== null) {
                $statuses[] = $aspek['status'];
            }
        }

        if (in_array('perhatian', $statuses, true)) return 'perhatian';
        if (in_array('evaluasi', $statuses, true)) return 'evaluasi';
        return 'aman';
    }

    /**
     * Hitung margin: (harga_produk - harga_final) / harga_produk * 100.
     */
    public function getMarginPersenAttribute(): float
    {
        if ($this->harga_produk <= 0) return 0;
        return round((($this->harga_produk - $this->harga_final) / $this->harga_produk) * 100, 1);
    }

    public function progressPerbaikan(): array
    {
        $total = 0;
        $selesai = 0;
        foreach (array_keys(self::ASPEK_AUDIT) as $key) {
            $aspek = $this->getAspek($key);
            // Hanya hitung aspek yang butuh perbaikan (evaluasi/perhatian)
            if (in_array($aspek['status'], ['evaluasi', 'perhatian'], true)) {
                $total++;
                if ($this->{"sudah_{$key}"}) $selesai++;
            }
        }
        return [
            'total'    => $total,
            'selesai'  => $selesai,
            'persen'   => $total > 0 ? (int) round(($selesai / $total) * 100) : 100,
        ];
    }
}