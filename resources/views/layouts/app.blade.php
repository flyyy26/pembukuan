<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pembukuan')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Geist:ital,wght@0,100..900;1,100..900&display=swap');
        * {
            font-family: 'Geist', sans-serif;
        }
        iconify-icon {
            display: inline-block;
            vertical-align: middle;
        }
        /* Custom scrollbar untuk sidebar */
        nav::-webkit-scrollbar {
            width: 4px;
        }
        nav::-webkit-scrollbar-track {
            background: transparent;
        }
        nav::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 4px;
        }
        nav::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

@php
    $navItems = [
        [
            'section' => 'Utama',
            'items' => [
                ['route' => 'shopee.index',  'label' => 'Laporan Shopee',  'icon' => 'mdi:shopping-outline'],
                ['route' => 'tiktok.index',  'label' => 'Laporan TikTok',  'icon' => 'mdi:music-note-outline'],
            ],
        ],
        [
            'section' => 'BRILink',
            'items' => [
                [
                    'route' => 'brilink.index',
                    'label' => 'Laporan BRILink',
                    'icon' => 'mdi:bank-outline',
                    'submenu' => [
                        ['route' => 'brilink.index', 'label' => 'Ringkasan & Saldo'],
                        ['route' => 'brilink.saldo-keseluruhan', 'label' => 'Data Saldo Keseluruhan'],
                        ['route' => 'brilink.pengeluaran', 'label' => 'Laporan Pengeluaran'],
                        ['route' => 'brilink.log-ppob', 'label' => 'Log Pulsa & PPOB'],
                        ['route' => 'brilink.log-setoran', 'label' => 'Log Setoran BRILink'],
                        ['route' => 'brilink.log-tarik-tunai', 'label' => 'Log Tarik Tunai'],
                        ['route' => 'brilink.profit', 'label' => 'Laporan Profit'],
                    ]
                ]
            ],
        ],
        [
            'section' => 'Data Master',
            'items' => [
                ['route' => 'import.index',        'label' => 'Import Shopee',  'icon' => 'mdi:import'],
                ['route' => 'tiktok.import',       'label' => 'Import TikTok',  'icon' => 'mdi:music-note'],
                ['route' => 'brilink.import',      'label' => 'Import BRILink', 'icon' => 'mdi:bank'],
                ['route' => 'products.index',      'label' => 'Produk',         'icon' => 'mdi:package-variant-closed'],
                ['route' => 'stores.index',        'label' => 'Toko',           'icon' => 'mdi:store-outline'],
            ],
        ],
    ];
@endphp

<div class="flex min-h-screen">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col fixed h-screen">

        {{-- Logo / Brand --}}
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <a href="/" class="flex items-center gap-2 font-bold text-lg text-gray-800">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm shadow-blue-500/30">
                    <iconify-icon icon="mdi:book-open-variant" class="text-white text-lg"></iconify-icon>
                </div>
                BukuBarokah
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4">
            @foreach($navItems as $group)
                <div class="mb-4">
                    <div class="px-6 mb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        {{ $group['section'] }}
                    </div>

                    @foreach($group['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['route']);
                            $hasSubmenu = isset($item['submenu']);
                            // Cek apakah ada submenu yang aktif
                            $isSubmenuActive = $hasSubmenu && collect($item['submenu'])->contains(fn($sub) => request()->routeIs($sub['route']));
                            if ($isSubmenuActive) $isActive = true;
                        @endphp

                        <a href="{{ route($item['route']) }}"
                           class="flex items-center justify-between px-6 py-2.5 text-sm transition
                                  {{ $isActive
                                      ? 'bg-blue-50 text-blue-700 font-medium border-r-2 border-blue-600'
                                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center gap-3">
                                <iconify-icon icon="{{ $item['icon'] }}" class="text-lg {{ $isActive ? 'text-blue-600' : 'text-gray-400' }}"></iconify-icon>
                                {{ $item['label'] }}
                            </div>
                            @if($hasSubmenu)
                                <iconify-icon icon="mdi:chevron-down" class="text-sm {{ $isActive ? 'text-blue-600' : 'text-gray-400' }}"></iconify-icon>
                            @endif
                        </a>

                        {{-- Render Submenu --}}
                        @if($hasSubmenu && ($isActive || request()->routeIs('brilink.*')))
                            <div class="bg-gray-50/50 py-1 border-r-2 border-transparent">
                                @foreach($item['submenu'] as $sub)
                                    @php $isSubActive = request()->routeIs($sub['route']); @endphp
                                    <a href="{{ route($sub['route']) }}"
                                       class="flex items-center gap-3 pl-14 pr-6 py-2 text-xs transition
                                              {{ $isSubActive
                                                  ? 'text-blue-700 font-medium'
                                                  : 'text-gray-500 hover:text-gray-800' }}">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $isSubActive ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
                                        {{ $sub['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </nav>

        {{-- Footer Sidebar — User Info + Logout --}}
        <div class="border-t border-gray-200 p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm shadow-blue-500/30">
                    <iconify-icon icon="mdi:account" class="text-white text-lg"></iconify-icon>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-gray-800 truncate">
                        {{ Auth::user()->name ?? 'Admin' }}
                    </div>
                    <div class="text-[10px] text-gray-400 truncate">
                        {{ Auth::user()->email ?? '' }}
                    </div>
                </div>
            </div>

            {{-- Logout Button --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-medium
                               text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-700
                               rounded-lg transition border border-red-100">
                    <iconify-icon icon="mdi:logout" class="text-base"></iconify-icon>
                    Logout
                </button>
            </form>

            <div class="text-[10px] text-gray-400 text-center mt-3">
                v1.0 · BukuProfit
            </div>
        </div>
    </aside>

    {{-- ============ MAIN CONTENT ============ --}}
    <div class="flex-1 ml-64">

        {{-- Topbar --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-10">

            {{-- Page Title --}}
            <h1 class="text-lg font-semibold text-gray-800">
                @yield('title', 'Pembukuan')
            </h1>

            {{-- User Dropdown --}}
            <div class="relative" id="userDropdownContainer">
                <button type="button"
                        onclick="toggleUserDropdown()"
                        class="flex items-center gap-3 pl-2 pr-3 py-1.5 rounded-lg hover:bg-gray-50 transition group">
                    {{-- Avatar --}}
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-sm shadow-blue-500/30 flex-shrink-0">
                        <iconify-icon icon="mdi:account" class="text-white text-lg"></iconify-icon>
                    </div>

                    {{-- Name (hidden on small screen) --}}
                    <div class="text-left hidden sm:block">
                        <div class="text-sm font-semibold text-gray-800 leading-tight">
                            {{ Auth::user()->name ?? 'Admin' }}
                        </div>
                        <div class="text-[10px] text-gray-400 leading-tight truncate max-w-[140px]">
                            {{ Auth::user()->email ?? '' }}
                        </div>
                    </div>

                    {{-- Chevron --}}
                    <iconify-icon icon="mdi:chevron-down"
                                  class="text-gray-400 text-base transition-transform group-hover:text-gray-600"></iconify-icon>
                </button>

                {{-- Dropdown Menu --}}
                <div id="userDropdown"
                     class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                    {{-- User Info --}}
                    <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-sm shadow-blue-500/30 flex-shrink-0">
                                <iconify-icon icon="mdi:account" class="text-white text-xl"></iconify-icon>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-800 truncate">
                                    {{ Auth::user()->name ?? 'Admin' }}
                                </div>
                                <div class="text-xs text-gray-500 truncate">
                                    {{ Auth::user()->email ?? '' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Menu Items --}}
                    <div class="py-1">
                        <div class="px-4 py-2 flex items-center gap-2 text-xs text-gray-500">
                            <iconify-icon icon="mdi:calendar" class="text-sm"></iconify-icon>
                            Login sejak: {{ now()->format('d M Y') }}
                        </div>
                    </div>

                    {{-- Logout --}}
                    <div class="border-t border-gray-100 p-2">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium
                                           text-red-600 hover:bg-red-50 rounded-lg transition">
                                <iconify-icon icon="mdi:logout" class="text-base"></iconify-icon>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="p-8">
            @yield('content')
        </main>
    </div>
</div>

<script>
    // ==================== USER DROPDOWN ====================
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Klik di luar = tutup dropdown
    document.addEventListener('click', function (e) {
        const container = document.getElementById('userDropdownContainer');
        const dropdown = document.getElementById('userDropdown');
        if (container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // ESC = tutup dropdown
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.getElementById('userDropdown').classList.add('hidden');
        }
    });
</script>

</body>
</html>