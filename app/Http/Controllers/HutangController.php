<?php

namespace App\Http\Controllers;

use App\Models\Hutang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HutangController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Hutang::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nama_pemberi_hutang', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            })
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('is_lunas', $status === 'lunas');
            })
            ->when($from, fn($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('tanggal', '<=', $to))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $hutangList = $query->get();

        $summary = [
            'total_hutang'     => (int) $hutangList->sum('nominal'),
            'total_belum_lunas'=> (int) $hutangList->where('is_lunas', false)->sum('nominal'),
            'total_lunas'      => (int) $hutangList->where('is_lunas', true)->sum('nominal'),
            'jumlah_data'      => $hutangList->count(),
        ];

        return view('hutang.index', compact('hutangList', 'summary', 'search', 'status', 'from', 'to'));
    }

    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama_pemberi_hutang' => 'required|string|max:255',
            'nominal' => 'required',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);
        if ($nominal <= 0) {
            return response()->json(['message' => 'Nominal tidak valid.'], 422);
        }

        Hutang::create([
            'tanggal' => $validated['tanggal'],
            'nama_pemberi_hutang' => $validated['nama_pemberi_hutang'],
            'nominal' => $nominal,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Hutang berhasil ditambahkan.']);
    }

    public function updateAjax(Request $request, Hutang $hutang)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama_pemberi_hutang' => 'required|string|max:255',
            'nominal' => 'required',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $nominal = (int) preg_replace('/[^0-9]/', '', (string) $request->nominal);
        
        $hutang->update([
            'tanggal' => $validated['tanggal'],
            'nama_pemberi_hutang' => $validated['nama_pemberi_hutang'],
            'nominal' => $nominal,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Hutang berhasil diperbarui.']);
    }

    public function bayarAjax(Hutang $hutang)
    {
        $hutang->update([
            'is_lunas' => true,
            'tanggal_lunas' => now()->format('Y-m-d'),
        ]);

        return response()->json(['success' => true, 'message' => 'Hutang berhasil ditandai lunas.']);
    }

    public function destroyAjax(Hutang $hutang)
    {
        $nama = $hutang->nama_pemberi_hutang;
        $hutang->delete();

        return response()->json(['success' => true, 'message' => "Hutang kepada '{$nama}' berhasil dihapus."]);
    }
}