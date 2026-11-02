@extends('layouts.app')

@section('title', 'Data Pengeluaran')

@section('content')
<div class="w-full min-w-0">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-rose-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="mdi:cash-minus" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-800">Data Pengeluaran</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catatan dan nota pengeluaran konveksi</p>
            </div>
        </div>
        <button type="button" onclick="openPengeluaranModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-rose-700 transition text-sm font-medium shadow-sm">
            <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon> Tambah Pengeluaran
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Pengeluaran</h3>
            <div class="text-[10px] sm:text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border w-max">{{ $pengeluaranList->count() }} Data</div>
        </div>

        @if($pengeluaranList->isEmpty())
            <div class="py-12 text-center text-gray-500">
                <iconify-icon icon="mdi:cash-minus" class="text-5xl text-gray-300 block m-auto mb-3 w-max"></iconify-icon>
                <p class="text-sm">Belum ada data pengeluaran.</p>
            </div>
        @else
            <div class="w-full overflow-x-auto">
                <table class="w-full text-xs sm:text-sm text-left min-w-[700px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap">No.</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap">Tanggal</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase whitespace-nowrap">Nama</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase">Keterangan</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-right whitespace-nowrap">Nominal</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-center">Nota</th>
                            <th class="py-2 px-3 text-[10px] font-semibold text-gray-500 uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pengeluaranList as $pengeluaran)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2 px-3 text-xs text-gray-500 whitespace-nowrap">#{{ $pengeluaran->id }}</td>
                                <td class="py-2 px-3 text-xs text-gray-700 whitespace-nowrap">{{ $pengeluaran->tanggal->format('d/m/Y') }}</td>
                                <td class="py-2 px-3 font-medium text-gray-800 whitespace-nowrap">{{ $pengeluaran->nama }}</td>
                                <td class="py-2 px-3 text-xs text-gray-600">{{ $pengeluaran->keterangan ?: '-' }}</td>
                                <td class="py-2 px-3 text-right font-bold text-rose-600 whitespace-nowrap">Rp {{ number_format($pengeluaran->nominal, 0, ',', '.') }}</td>
                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                    <a href="{{ route('konveksi.pengeluaran.invoice-pdf', $pengeluaran) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium border border-blue-200">
                                        <iconify-icon icon="mdi:file-pdf-box" class="text-sm"></iconify-icon> Cetak PDF
                                    </a>
                                </td>
                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                    <button type="button" onclick="deletePengeluaran({{ $pengeluaran->id }})" class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium border border-red-200">
                                        <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="4" class="py-2 px-3 text-right font-bold text-gray-800 text-xs uppercase">Total Pengeluaran</td>
                            <td class="py-2 px-3 text-right font-bold text-rose-600 whitespace-nowrap">Rp {{ number_format($pengeluaranList->sum('nominal'), 0, ',', '.') }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>

<div id="pengeluaranModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closePengeluaranModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-rose-50">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:cash-minus" class="text-rose-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Tambah Pengeluaran</h3>
            </div>
            <button type="button" onclick="closePengeluaranModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="pengeluaranForm" onsubmit="submitPengeluaran(event)">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorPengeluaran"></div>

                <div>
                    <label for="pengeluaranNama" class="block text-xs font-medium text-gray-600 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="pengeluaranNama" required maxlength="100" placeholder="Nama penerima / keperluan"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="pengeluaranNominal" class="block text-xs font-medium text-gray-600 mb-1">Nominal <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm font-medium">Rp</span>
                        <input type="text" name="nominal" id="pengeluaranNominal" required inputmode="numeric" placeholder="0"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="pengeluaranTanggal" class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" id="pengeluaranTanggal" required value="{{ now()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>

                <div>
                    <label for="pengeluaranKeterangan" class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                    <textarea name="keterangan" id="pengeluaranKeterangan" rows="3" maxlength="255" placeholder="Keterangan pengeluaran (opsional)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closePengeluaranModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitPengeluaran" class="bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-rose-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan & Cetak Nota
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const pengeluaranCsrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const deletePengeluaranUrl = @json(route('konveksi.pengeluaran.data.destroy-ajax', ['pengeluaran' => '__ID__']));

    function openPengeluaranModal() {
        document.getElementById('pengeluaranForm').reset();
        document.getElementById('pengeluaranTanggal').value = new Date().toISOString().slice(0, 10);
        document.getElementById('modalErrorPengeluaran').classList.add('hidden');
        document.getElementById('pengeluaranModal').classList.remove('hidden');
        document.getElementById('pengeluaranModal').classList.add('flex');
    }

    function closePengeluaranModal() {
        document.getElementById('pengeluaranModal').classList.add('hidden');
        document.getElementById('pengeluaranModal').classList.remove('flex');
    }

    async function deletePengeluaran(id) {
        if (!confirm('Yakin ingin menghapus data pengeluaran ini?')) return;

        try {
            const response = await fetch(deletePengeluaranUrl.replace('__ID__', id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': pengeluaranCsrfToken, 'Accept': 'application/json' },
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menghapus pengeluaran.');
            window.location.reload();
        } catch (deleteError) {
            alert(deleteError.message);
        }
    }

    document.getElementById('pengeluaranNominal').addEventListener('input', function () {
        const digits = this.value.replace(/[^0-9]/g, '');
        this.value = digits ? new Intl.NumberFormat('id-ID').format(digits) : '';
    });

    async function submitPengeluaran(event) {
        event.preventDefault();
        const form = event.target;
        const button = document.getElementById('btnSubmitPengeluaran');
        const error = document.getElementById('modalErrorPengeluaran');
        const originalButton = button.innerHTML;
        const pdfWindow = window.open('', '_blank');
        const formData = new FormData(form);
        formData.set('nominal', document.getElementById('pengeluaranNominal').value.replace(/[^0-9]/g, ''));

        button.disabled = true;
        button.textContent = 'Menyimpan...';
        error.classList.add('hidden');

        try {
            const response = await fetch("{{ route('konveksi.pengeluaran.data.store-ajax') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': pengeluaranCsrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal menyimpan pengeluaran.');

            const pdfUrl = `/konveksi/pengeluaran/${data.pengeluaran_id}/invoice-pdf`;
            if (pdfWindow) {
                pdfWindow.location = pdfUrl;
            } else {
                window.location.href = pdfUrl;
                return;
            }

            window.location.reload();
        } catch (submitError) {
            if (pdfWindow) pdfWindow.close();
            error.textContent = submitError.message;
            error.classList.remove('hidden');
            button.disabled = false;
            button.innerHTML = originalButton;
        }
    }
</script>
@endsection
