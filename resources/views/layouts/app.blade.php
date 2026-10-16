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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

@php
    $navItems = [
        [
            'section' => 'Utama',
            'items' => [
                ['route' => 'shopee.index',   'label' => 'Laporan Shopee', 'icon' => 'mdi:shopping-outline'],
            ],
        ],
        [
            'section' => 'Data Master',
            'items' => [
                ['route' => 'import.index',   'label' => 'Import',          'icon' => 'mdi:import'],
                ['route' => 'products.index', 'label' => 'Produk',          'icon' => 'mdi:package-variant-closed'],
                ['route' => 'stores.index',   'label' => 'Toko',            'icon' => 'mdi:store-outline'],
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
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                    <iconify-icon icon="mdi:book-open-variant" class="text-white text-lg"></iconify-icon>
                </div>
                BukuProfit
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
                            $active = request()->routeIs($item['route']);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-6 py-2.5 text-sm transition
                                  {{ $active
                                      ? 'bg-blue-50 text-blue-700 font-medium border-r-2 border-blue-600'
                                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <iconify-icon
                                icon="{{ $item['icon'] }}"
                                class="text-lg {{ $active ? 'text-blue-600' : 'text-gray-400' }}">
                            </iconify-icon>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        {{-- Footer Sidebar --}}
        <div class="border-t border-gray-200 p-4">
            <div class="text-xs text-gray-400 text-center">
                v1.0 · Pembukuan
            </div>
        </div>
    </aside>

    {{-- ============ MAIN CONTENT ============ --}}
    <div class="flex-1 ml-64">

        {{-- Topbar --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-10">
            <h1 class="text-lg font-semibold text-gray-800">
                @yield('title', 'Pembukuan')
            </h1>

            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">Admin</span>
                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <iconify-icon icon="mdi:account" class="text-gray-500"></iconify-icon>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="p-8">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>