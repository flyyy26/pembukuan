<?php

namespace App\Http\Controllers;

use App\Models\ZakatPembayaran;
use Illuminate\Http\Request;

class ZakatPembayaranController extends Controller
{
    public function index(Request $request)
    {
        $tahunFilter = $request->input('tahun', 'all');

        // Ambil list tahun unik dari data pembayaran
        $tahunList = ZakatPembayaran::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $query = ZakatPembayaran::query()
            ->when($tahunFilter !== 'all', function($q) use ($tahunFilter) {
                $q->whereYear('tanggal', $tahunFilter);
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $pembayaranList = $query->get();

        return view('zakat.laporan-bayar', compact('pembayaranList', 'tahunList', 'tahunFilter'));
    }
    public function destroy(ZakatPembayaran $pembayaran)
    {
        try {
            $tanggal = optional($pembayaran->tanggal)->translatedFormat('d F Y') ?? '-';
            $pembayaran->delete();

            return response()->json([
                'success' => true,
                'message' => "Laporan pembayaran zakat tanggal {$tanggal} berhasil dihapus.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ], 500);
        }
    }
}