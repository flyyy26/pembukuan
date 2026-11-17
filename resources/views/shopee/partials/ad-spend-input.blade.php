<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="mb-3">
        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
            <iconify-icon icon="mdi:bullhorn-outline" class="text-purple-600 text-xl"></iconify-icon>
        </div>
        
    </div>
    <div class="text-xs text-gray-500">Biaya Iklan</div>
    <form method="POST" action="{{ route('shopee.ad-spend.save') }}">
        @csrf
        <input type="hidden" name="store" value="{{ $storeId }}">
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">
        <input type="hidden" name="tab" value="{{ $activeTab }}">

        <div class="flex items-center gap-2">
            <div class="relative min-w-0 flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">Rp</span>
                <input id="{{ $inputId }}" name="biaya_iklan" type="text" inputmode="numeric" pattern="[0-9.]+"
                       autocomplete="off" data-rupiah-input required
                       value="{{ old('biaya_iklan', number_format($summary['biaya_iklan'], 0, ',', '.')) }}"
                       class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <button type="submit"
                    class="shrink-0 rounded-lg bg-purple-600 px-3 py-2 text-sm font-medium text-white hover:bg-purple-700 transition">
                Simpan
            </button>
        </div>
        @error('biaya_iklan')
            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
        @enderror
    </form>
</div>
