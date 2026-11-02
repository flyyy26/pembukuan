<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Hanca extends Model
{
    protected $fillable = [
        'konveksi_karyawan_id', 'kategori', 'tanggal'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(KonveksiKaryawan::class, 'konveksi_karyawan_id');
    }

    public function details()
    {
        return $this->hasMany(HancaDetail::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(HancaPembayaran::class);
    }

    // --- ACCESSORS ---

    public function getTotalAttribute()
    {
        return $this->details->sum('total');
    }

    public function getTotalQtyAttribute()
    {
        return $this->details->sum('jumlah_hanca');
    }

    public function getSisaHancaAttribute()
    {
        return $this->details->sum('sisa_hanca');
    }

    public function getSisaNominalAttribute()
    {
        // Total - (Dibayar)
        $dibayar = $this->total_dibayar;
        return max(0, $this->total - $dibayar);
    }

    public function getTotalDibayarAttribute()
    {
        return $this->pembayarans()
            ->where('tipe', 'bayaran') // Hanya bayaran
            ->sum('nominal');
    }

    // ✅ HAPUS: getTotalKasbonAttribute()

    public function getSisaAttribute()
    {
        return $this->sisa_nominal;
    }

    public function getDurasiAttribute()
    {
        if (!$this->tanggal) return 0;
        return (int) Carbon::parse($this->tanggal)->startOfDay()->diffInDays(now()->startOfDay());
    }

    public function getDurasiColorAttribute()
    {
        $durasi = $this->durasi;
        if ($durasi <= 7) return 'green';
        if ($durasi <= 14) return 'yellow';
        return 'red';
    }

    /**
     * Recalculate sisa Hanca & Nominal (di setiap detail)
     */
    public function recalculateSisa(): void
    {
        foreach ($this->details as $detail) {
            $dibayarNominal = $this->pembayarans()
                ->where('hanca_detail_id', $detail->id)
                ->where('tipe', 'bayaran')
                ->sum('nominal');
                
            $dibayarQty = $this->pembayarans()
                ->where('hanca_detail_id', $detail->id)
                ->where('tipe', 'bayaran')
                ->sum('qty_setor');

            $sisaNominal = max(0, $detail->total - $dibayarNominal);
            $sisaHanca = max(0, $detail->jumlah_hanca - $dibayarQty);

            $detail->update([
                'sisa_nominal' => $sisaNominal,
                'sisa_hanca'   => $sisaHanca,
            ]);
        }
    }

    public function recalculateSisaPartial($qtyDibayar, $nominalDibayar): void
    {
        // Distribusikan qty yang dibayar ke detail (FIFO)
        $sisaQty = $qtyDibayar;
        
        foreach ($this->details as $detail) {
            if ($sisaQty <= 0) break;
            
            $kurang = min($sisaQty, $detail->sisa_hanca);
            $detail->sisa_hanca -= $kurang;
            $detail->sisa_nominal = max(0, $detail->sisa_nominal - ($kurang * $detail->harga));
            $detail->save();
            
            $sisaQty -= $kurang;
        }
        
        // Update sisa nominal hanca keseluruhan
        $this->refresh();
    }
}