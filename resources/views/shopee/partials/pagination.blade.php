@if($totalItems > $perPage)
    @php
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        $firstItem = ($currentPage - 1) * $perPage + 1;
        $lastItem = min($currentPage * $perPage, $totalItems);
        $pageUrl = function ($targetPage) use ($pageParameter) {
            $query = request()->query();
            $query[$pageParameter] = $targetPage;

            return route('shopee.index', $query);
        };
    @endphp
    <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-xs text-gray-500">
            Menampilkan {{ $firstItem }}–{{ $lastItem }} dari {{ $totalItems }} {{ $itemLabel }}
        </div>
        <nav class="flex flex-wrap gap-1" aria-label="Pagination">
            @if($currentPage > 1)
                <a href="{{ $pageUrl($currentPage - 1) }}"
                   aria-label="Halaman sebelumnya"
                   class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                    &laquo;
                </a>
            @endif

            @if($startPage > 1)
                <a href="{{ $pageUrl(1) }}"
                   class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">1</a>
                @if($startPage > 2)
                    <span class="px-2 py-1.5 text-gray-400">&hellip;</span>
                @endif
            @endif

            @for($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++)
                <a href="{{ $pageUrl($pageNumber) }}"
                   @if($pageNumber === $currentPage) aria-current="page" @endif
                   class="px-3 py-1.5 rounded-lg text-sm transition
                          {{ $pageNumber === $currentPage
                              ? 'bg-blue-600 text-white font-medium shadow-sm'
                              : 'border border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                    {{ $pageNumber }}
                </a>
            @endfor

            @if($endPage < $totalPages)
                @if($endPage < $totalPages - 1)
                    <span class="px-2 py-1.5 text-gray-400">&hellip;</span>
                @endif
                <a href="{{ $pageUrl($totalPages) }}"
                   class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                    {{ $totalPages }}
                </a>
            @endif

            @if($currentPage < $totalPages)
                <a href="{{ $pageUrl($currentPage + 1) }}"
                   aria-label="Halaman berikutnya"
                   class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                    &raquo;
                </a>
            @endif
        </nav>
    </div>
@endif
