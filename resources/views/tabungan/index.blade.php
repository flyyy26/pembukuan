@extends('layouts.app')

@section('title', 'Laporan Tabungan')

@section('content')
<div class="w-full">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center">
                <iconify-icon icon="mdi:piggy-bank" class="text-white text-xl"></iconify-icon>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Laporan Tabungan</h2>
                <p class="text-sm text-gray-500">Kelola setoran & penarikan tabungan</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="openAccountModal()" class="inline-flex items-center gap-2 bg-white border border-emerald-600 text-emerald-700 px-4 py-2 rounded-lg hover:bg-emerald-50 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:account-plus" class="text-lg"></iconify-icon>
                Tambah Akun
            </button>
            <button type="button" onclick="openTransactionModal()" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
                <iconify-icon icon="mdi:plus" class="text-lg"></iconify-icon>
                Tambah Transaksi
            </button>
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-sm p-5 text-white">
            <div class="flex items-center gap-2 mb-2">
                <iconify-icon icon="mdi:piggy-bank-outline" class="text-xl"></iconify-icon>
                <span class="text-xs text-white/80">Total Saldo Tabungan</span>
            </div>
            <div class="text-2xl font-bold">Rp {{ number_format($summary['total_saldo'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-2">
                <iconify-icon icon="mdi:arrow-down-circle" class="text-emerald-600 text-xl"></iconify-icon>
                <span class="text-xs text-gray-500">Setoran Bulan Ini</span>
            </div>
            <div class="text-2xl font-bold text-emerald-600">Rp {{ number_format($summary['setoran_bulan_ini'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-2">
                <iconify-icon icon="mdi:arrow-up-circle" class="text-red-600 text-xl"></iconify-icon>
                <span class="text-xs text-gray-500">Penarikan Bulan Ini</span>
            </div>
            <div class="text-2xl font-bold text-red-600">Rp {{ number_format($summary['penarikan_bulan_ini'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-2">
                <iconify-icon icon="mdi:account-group" class="text-blue-600 text-xl"></iconify-icon>
                <span class="text-xs text-gray-500">Akun Aktif</span>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ $summary['jumlah_akun_aktif'] }} <span class="text-xs font-normal text-gray-500">Akun</span></div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Akun</label>
                <select name="account" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="all" {{ $accountId === 'all' ? 'selected' : '' }}>Semua Akun</option>
                    @foreach($accounts as $a)
                        <option value="{{ $a->id }}" {{ $accountId == $a->id ? 'selected' : '' }}>{{ $a->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                <select name="tipe" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                    <option value="semua" {{ $tipe === 'semua' ? 'selected' : '' }}>Semua</option>
                    <option value="setoran" {{ $tipe === 'setoran' ? 'selected' : '' }}>Setoran</option>
                    <option value="penarikan" {{ $tipe === 'penarikan' ? 'selected' : '' }}>Penarikan</option>
                </select>
            </div>
            <div class="w-full md:flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <iconify-icon icon="mdi:magnify" class="text-gray-400 text-lg"></iconify-icon>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama akun / keterangan..."
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-emerald-600 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 transition text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:filter" class="text-lg"></iconify-icon> Filter
                </button>
                @if($fromInput || $toInput || $search || $accountId !== 'all' || $tipe !== 'semua')
                    <a href="{{ route('tabungan.index') }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg text-center transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="flex border-b border-gray-100">
            <button type="button" id="tabBtnSaldo" onclick="switchTab('saldo')" class="px-5 py-3 text-sm font-medium border-b-2 border-emerald-600 text-emerald-700 bg-emerald-50/50">
                <iconify-icon icon="mdi:wallet" class="text-lg mr-1"></iconify-icon> Saldo Per Akun
            </button>
            <button type="button" id="tabBtnTransaksi" onclick="switchTab('transaksi')" class="px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:bg-gray-50">
                <iconify-icon icon="mdi:history" class="text-lg mr-1"></iconify-icon> Riwayat Transaksi
            </button>
        </div>

        {{-- TAB SALDO --}}
        <div id="tabSaldo" class="p-5">
            @if($accountsWithSaldo->isEmpty())
                <div class="py-12 text-center text-gray-500">
                    <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                    Belum ada akun tabungan.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Nama Akun</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Total Setoran</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Total Penarikan</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Saldo</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($accountsWithSaldo as $acc)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-800">
                                    {{ $acc['nama'] }}
                                    @if(!$acc['is_active'])
                                        <span class="ml-2 text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-600">{{ $acc['keterangan'] ?? '-' }}</td>
                                <td class="py-3 px-4 text-right text-emerald-600">Rp {{ number_format($acc['total_setoran'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-red-600">Rp {{ number_format($acc['total_penarikan'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp {{ number_format($acc['saldo'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" onclick="openTransactionModal({{ $acc['id'] }})" class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:plus" class="text-sm"></iconify-icon> Trx
                                        </button>
                                        <button type="button" onclick="openAccountModal({{ $acc['id'] }}, '{{ addslashes($acc['nama']) }}', '{{ addslashes($acc['keterangan'] ?? '') }}', {{ $acc['is_active'] ? 'true' : 'false' }})" class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                        </button>
                                        <button type="button" onclick="deleteAccount({{ $acc['id'] }}, '{{ addslashes($acc['nama']) }}')" class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- TAB TRANSAKSI --}}
        <div id="tabTransaksi" class="p-5 hidden">
            @if($transactions->isEmpty())
                <div class="py-12 text-center text-gray-500">
                    <iconify-icon icon="mdi:inbox-outline" class="text-4xl text-gray-300 block m-auto mb-2 w-max"></iconify-icon>
                    Belum ada transaksi pada filter ini.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Akun</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Nominal</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($transactions as $trx)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-700">{{ $trx->tanggal->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-medium text-gray-800">{{ $trx->account->nama ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($trx->tipe === 'setoran')
                                        <span class="inline-flex items-center gap-1 text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:arrow-down" class="text-sm"></iconify-icon> Setoran
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-medium">
                                            <iconify-icon icon="mdi:arrow-up" class="text-sm"></iconify-icon> Penarikan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $trx->tipe === 'setoran' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $trx->tipe === 'setoran' ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-600">{{ $trx->keterangan ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1 flex-wrap justify-center">
                                        <a href="{{ route('tabungan.transaction.invoice-pdf', $trx->id) }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:printer" class="text-sm"></iconify-icon> Cetak
                                        </a>
                                        <button type="button" onclick="openEditTransactionModal({{ $trx->id }}, {{ $trx->nominal }}, '{{ $trx->tanggal->format('Y-m-d') }}', '{{ addslashes($trx->keterangan ?? '') }}')" class="inline-flex items-center gap-1 text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:pencil" class="text-sm"></iconify-icon> Edit
                                        </button>
                                        <button type="button" onclick="deleteTransaction({{ $trx->id }})" class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition">
                                            <iconify-icon icon="mdi:delete" class="text-sm"></iconify-icon> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>

{{-- MODAL AKUN --}}
<div id="accountModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeAccountModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-2">
                <iconify-icon id="accountModalIcon" icon="mdi:account-plus" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 id="accountModalTitle" class="font-bold text-gray-800">Tambah Akun Tabungan</h3>
            </div>
            <button type="button" onclick="closeAccountModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>
        <form id="accountForm" onsubmit="submitAccountForm(event)">
            @csrf
            <input type="hidden" id="accountMode" value="create">
            <input type="hidden" id="accountId" value="">
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="accountError"></div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Akun <span class="text-red-500">*</span></label>
                    <input type="text" id="accountNama" name="nama" required placeholder="cth: Ahmad / Tabungan Pendidikan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan (opsional)</label>
                    <input type="text" id="accountKeterangan" name="keterangan" placeholder="Catatan tambahan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="accountIsActive" name="is_active" value="1" checked class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeAccountModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitAccount" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitAccountText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL TRANSAKSI --}}
<div id="transactionModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeTransactionModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-2">
                <iconify-icon id="trxModalIcon" icon="mdi:plus-circle" class="text-emerald-600 text-lg"></iconify-icon>
                <h3 id="trxModalTitle" class="font-bold text-gray-800">Tambah Transaksi</h3>
            </div>
            <button type="button" onclick="closeTransactionModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>
        <form id="transactionForm" onsubmit="submitTransactionForm(event)">
            @csrf
            <input type="hidden" id="trxMode" value="create">
            <input type="hidden" id="trxId" value="">
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="trxError"></div>
                <div id="trxAccountWrapper">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Akun <span class="text-red-500">*</span></label>
                    <select id="trxAccount" name="tabungan_account_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}">{{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="trxTipeWrapper">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="tipe" value="setoran" class="peer sr-only" checked>
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                <iconify-icon icon="mdi:arrow-down-circle" class="text-emerald-600 text-xl block mx-auto mb-1"></iconify-icon>
                                <div class="text-xs font-medium">Setoran</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="tipe" value="penarikan" class="peer sr-only">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center transition peer-checked:border-red-500 peer-checked:bg-red-50">
                                <iconify-icon icon="mdi:arrow-up-circle" class="text-red-600 text-xl block mx-auto mb-1"></iconify-icon>
                                <div class="text-xs font-medium">Penarikan</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" id="trxTanggal" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nominal <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                        </div>
                        <input type="text" id="trxNominal" name="nominal" required placeholder="0" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm font-bold">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan (opsional)</label>
                    <input type="text" id="trxKeterangan" name="keterangan" placeholder="Catatan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitTrx" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> <span id="btnSubmitTrxText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ===== TABS =====
function switchTab(tab) {
    document.getElementById('tabSaldo').classList.toggle('hidden', tab !== 'saldo');
    document.getElementById('tabTransaksi').classList.toggle('hidden', tab !== 'transaksi');
    document.getElementById('tabBtnSaldo').classList.toggle('border-emerald-600', tab === 'saldo');
    document.getElementById('tabBtnSaldo').classList.toggle('text-emerald-700', tab === 'saldo');
    document.getElementById('tabBtnSaldo').classList.toggle('bg-emerald-50/50', tab === 'saldo');
    document.getElementById('tabBtnSaldo').classList.toggle('border-transparent', tab !== 'saldo');
    document.getElementById('tabBtnSaldo').classList.toggle('text-gray-600', tab !== 'saldo');
    document.getElementById('tabBtnTransaksi').classList.toggle('border-emerald-600', tab === 'transaksi');
    document.getElementById('tabBtnTransaksi').classList.toggle('text-emerald-700', tab === 'transaksi');
    document.getElementById('tabBtnTransaksi').classList.toggle('bg-emerald-50/50', tab === 'transaksi');
    document.getElementById('tabBtnTransaksi').classList.toggle('border-transparent', tab !== 'transaksi');
    document.getElementById('tabBtnTransaksi').classList.toggle('text-gray-600', tab !== 'transaksi');
}

// ===== ACCOUNT MODAL =====
function openAccountModal(id = null, nama = '', keterangan = '', isActive = true) {
    document.getElementById('accountForm').reset();
    document.getElementById('accountError').classList.add('hidden');
    document.getElementById('accountId').value = id || '';
    document.getElementById('accountMode').value = id ? 'edit' : 'create';
    document.getElementById('accountModalTitle').textContent = id ? 'Edit Akun' : 'Tambah Akun Tabungan';
    document.getElementById('accountModalIcon').setAttribute('icon', id ? 'mdi:pencil' : 'mdi:account-plus');
    document.getElementById('btnSubmitAccountText').textContent = id ? 'Simpan Perubahan' : 'Simpan';
    document.getElementById('accountNama').value = nama;
    document.getElementById('accountKeterangan').value = keterangan;
    document.getElementById('accountIsActive').checked = isActive;
    document.getElementById('accountModal').classList.remove('hidden');
    document.getElementById('accountModal').classList.add('flex');
}

function closeAccountModal() {
    document.getElementById('accountModal').classList.add('hidden');
    document.getElementById('accountModal').classList.remove('flex');
}

async function submitAccountForm(e) {
    e.preventDefault();
    const mode = document.getElementById('accountMode').value;
    const id = document.getElementById('accountId').value;
    const btn = document.getElementById('btnSubmitAccount');
    const btnText = document.getElementById('btnSubmitAccountText');
    const errDiv = document.getElementById('accountError');
    const orig = btnText.textContent;
    btn.disabled = true; btnText.textContent = 'Menyimpan...';
    errDiv.classList.add('hidden');

    const formData = new FormData(e.target);
    if (!document.getElementById('accountIsActive').checked) {
        formData.set('is_active', '0');
    } else {
        formData.set('is_active', '1');
    }

    let url = "{{ route('tabungan.account.store-ajax') }}";
    if (mode === 'edit') {
        url = `/tabungan/account/${id}/ajax`;
        formData.append('_method', 'PUT');
    }

    try {
        const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: formData });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal.');
        closeAccountModal();
        window.location.reload();
    } catch (err) {
        errDiv.textContent = err.message;
        errDiv.classList.remove('hidden');
        btn.disabled = false; btnText.textContent = orig;
    }
}

async function deleteAccount(id, nama) {
    if (!confirm(`Yakin hapus akun "${nama}"?\n\nSEMUA transaksi akun ini akan ikut terhapus permanen.\nTotal Saldo, Setoran & Penarikan bulan ini akan otomatis menyesuaikan.`)) {
        return;
    }

    try {
        const res = await fetch(`/tabungan/account/${id}/ajax`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!res.ok) {
            alert(data.message || 'Gagal menghapus.');
            return;
        }
        // Reload → summary & riwayat otomatis recalculate dari DB
        window.location.reload();
    } catch (err) {
        alert('Gagal: ' + err.message);
    }
}

// ===== TRANSACTION MODAL =====
function openTransactionModal(accountId = null) {
    document.getElementById('transactionForm').reset();
    document.getElementById('trxError').classList.add('hidden');
    document.getElementById('trxMode').value = 'create';
    document.getElementById('trxId').value = '';
    document.getElementById('trxModalTitle').textContent = 'Tambah Transaksi';
    document.getElementById('trxModalIcon').setAttribute('icon', 'mdi:plus-circle');
    document.getElementById('btnSubmitTrxText').textContent = 'Simpan';
    document.getElementById('trxAccountWrapper').classList.remove('hidden');
    document.getElementById('trxTipeWrapper').classList.remove('hidden');
    document.getElementById('trxAccount').disabled = false;
    if (accountId) document.getElementById('trxAccount').value = accountId;
    document.getElementById('trxTanggal').value = new Date().toISOString().slice(0, 10);
    document.getElementById('trxNominal').value = '';
    document.getElementById('transactionModal').classList.remove('hidden');
    document.getElementById('transactionModal').classList.add('flex');
}

function openEditTransactionModal(id, nominal, tanggal, keterangan) {
    document.getElementById('transactionForm').reset();
    document.getElementById('trxError').classList.add('hidden');
    document.getElementById('trxMode').value = 'edit';
    document.getElementById('trxId').value = id;
    document.getElementById('trxModalTitle').textContent = 'Edit Transaksi';
    document.getElementById('trxModalIcon').setAttribute('icon', 'mdi:pencil');
    document.getElementById('btnSubmitTrxText').textContent = 'Simpan Perubahan';
    document.getElementById('trxAccountWrapper').classList.add('hidden');
    document.getElementById('trxTipeWrapper').classList.add('hidden');
    document.getElementById('trxTanggal').value = tanggal;
    document.getElementById('trxNominal').value = new Intl.NumberFormat('id-ID').format(nominal);
    document.getElementById('trxKeterangan').value = keterangan;
    document.getElementById('transactionModal').classList.remove('hidden');
    document.getElementById('transactionModal').classList.add('flex');
}

function closeTransactionModal() {
    document.getElementById('transactionModal').classList.add('hidden');
    document.getElementById('transactionModal').classList.remove('flex');
}

document.getElementById('trxNominal')?.addEventListener('input', function () {
    const v = this.value.replace(/[^0-9]/g, '');
    this.value = v ? new Intl.NumberFormat('id-ID').format(v) : '';
});

async function submitTransactionForm(e) {
    e.preventDefault();
    const mode = document.getElementById('trxMode').value;
    const id = document.getElementById('trxId').value;
    const btn = document.getElementById('btnSubmitTrx');
    const btnText = document.getElementById('btnSubmitTrxText');
    const errDiv = document.getElementById('trxError');
    const orig = btnText.textContent;
    btn.disabled = true; btnText.textContent = 'Menyimpan...';
    errDiv.classList.add('hidden');

    const formData = new FormData(e.target);
    formData.set('nominal', document.getElementById('trxNominal').value.replace(/[^0-9]/g, ''));

    let url = "{{ route('tabungan.transaction.store-ajax') }}";
    if (mode === 'edit') {
        url = `/tabungan/transaction/${id}/ajax`;
        formData.append('_method', 'PUT');
    }

    try {
        const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: formData });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Gagal.');
        closeTransactionModal();
        window.location.reload();
    } catch (err) {
        errDiv.textContent = err.message;
        errDiv.classList.remove('hidden');
        btn.disabled = false; btnText.textContent = orig;
    }
}

async function deleteTransaction(id) {
    if (!confirm('Yakin hapus transaksi ini?')) return;
    try {
        const res = await fetch(`/tabungan/transaction/${id}/ajax`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        const data = await res.json();
        if (!res.ok) { alert(data.message); return; }
        window.location.reload();
    } catch (err) { alert('Gagal: ' + err.message); }
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeAccountModal(); closeTransactionModal(); }
});
</script>
@endsection