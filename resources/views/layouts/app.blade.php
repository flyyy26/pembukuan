<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pembukuan')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">

@php
    $user = Auth::user();
    $isAdmin = $user && $user->isAdmin();

    // DEBUG: aktifkan sementara untuk melihat permission user di browser console
    if ($user && !$isAdmin) {
        $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
        echo "<script>console.log('USER PERMISSIONS:', " . json_encode($userPerms) . ");</script>";
    }

    // Helper: cek permission dengan aman
    $canView = function ($permission) use ($user, $isAdmin) {
        if (!$user) return false;
        if ($isAdmin) return true;
        return $user->can(trim($permission));
    };

    $navItems = [
        [
            'section' => 'Utama',
            'items' => array_values(array_filter([
                $canView('view shopee') ? ['route' => 'shopee.index', 'label' => 'Laporan Shopee', 'icon' => 'mdi:shopping-outline'] : null,
                ($canView('view tiktok') || $canView('view tiktok live')) ? [
                    'route' => $canView('view tiktok') ? 'tiktok.index' : 'tiktok.live.index',
                    'label' => 'Laporan TikTok',
                    'icon' => 'mdi:music-note-outline',
                    'submenu' => array_values(array_filter([
                        $canView('view tiktok') ? ['route' => 'tiktok.index', 'label' => 'Ringkasan & Transaksi'] : null,
                        $canView('view tiktok live') ? ['route' => 'tiktok.live.index', 'label' => 'Laporan Live'] : null,
                    ])),
                ] : null,
                $canView('view web-sales') ? ['route' => 'reports.web-sales.index', 'label' => 'Laporan Penjualan Web', 'icon' => 'mdi:web'] : null,
                
                $canView('view konveksi') ? [
                    'route' => 'konveksi.index',
                    'label' => 'Laporan Konveksi',
                    'icon' => 'mdi:scissors-cutting',
                    'submenu' => array_values(array_filter([
                        $canView('view konveksi') ? ['route' => 'konveksi.index', 'label' => 'Data Setor Hanca', 'icon' => 'mdi:clipboard-list'] : null,
                        $canView('view konveksi') ? ['route' => 'konveksi.data-bayaran', 'label' => 'Data Bayaran', 'icon' => 'mdi:cash-check'] : null,
                        $canView('view konveksi') ? ['route' => 'konveksi.hanca-selesai', 'label' => 'Hanca Selesai', 'icon' => 'mdi:check-all'] : null,
                        $canView('view konveksi') ? ['route' => 'konveksi.kasbon', 'label' => 'Data Kasbon', 'icon' => 'mdi:cash-remove'] : null,
                        $canView('view konveksi') ? ['route' => 'konveksi.pengeluaran', 'label' => 'Data Pengeluaran', 'icon' => 'mdi:cash-minus'] : null,
                    ])),
                ] : null,
            ])),
        ],
        [
            'section' => 'BRILink',
            'items' => array_values(array_filter([
                ($canView('view brilink') || $canView('view brilink saldo') || $canView('view brilink pertumbuhan') || $canView('view brilink pengeluaran') || $canView('view brilink ppob') || $canView('view brilink setoran') || $canView('view brilink tarik-tunai') || $canView('view brilink profit') || $canView('view brilink brangkas')) ? [
                    'route' => 'brilink.index',
                    'label' => 'Laporan BRILink',
                    'icon' => 'mdi:bank-outline',
                    'submenu' => array_values(array_filter([
                        $canView('view brilink') ? ['route' => 'brilink.index', 'label' => 'Ringkasan & Saldo'] : null,
                        $canView('view brilink saldo') ? ['route' => 'brilink.saldo-keseluruhan', 'label' => 'Data Saldo Keseluruhan'] : null,
                        $canView('view brilink pertumbuhan') ? ['route' => 'brilink.pertumbuhan-saldo', 'label' => 'Pertumbuhan Saldo'] : null,
                        $canView('view brilink pengeluaran') ? ['route' => 'brilink.pengeluaran', 'label' => 'Laporan Pengeluaran'] : null,
                        $canView('view brilink ppob') ? ['route' => 'brilink.log-ppob', 'label' => 'Log Pulsa & PPOB'] : null,
                        $canView('view brilink setoran') ? ['route' => 'brilink.log-setoran', 'label' => 'Log Setoran BRILink'] : null,
                        $canView('view brilink tarik-tunai') ? ['route' => 'brilink.log-tarik-tunai', 'label' => 'Log Tarik Tunai'] : null,
                        $canView('view brilink profit') ? ['route' => 'brilink.profit', 'label' => 'Laporan Profit'] : null,
                        $canView('view brilink brangkas') ? ['route' => 'brilink.laporan-brangkas', 'label' => 'Laporan Brangkas'] : null,
                    ])),
                ] : null,
            ])),
        ],
        [
            'section' => 'Aset & Kewajiban',
            'items' => array_values(array_filter([
                $canView('view tabungan') ? ['route' => 'tabungan.index', 'label' => 'Laporan Tabungan', 'icon' => 'mdi:piggy-bank'] : null,
                $canView('view hutang') ? ['route' => 'hutang.index', 'label' => 'Laporan Hutang', 'icon' => 'mdi:file-document-outline'] : null,
                $canView('view piutang') ? ['route' => 'piutang.index', 'label' => 'Laporan Piutang', 'icon' => 'mdi:account-cash'] : null,
                $canView('view zakat') ? ['route' => 'zakat.index', 'label' => 'Laporan Zakat', 'icon' => 'mdi:hand-heart'] : null,
            ])),
        ],
        [
            'section' => 'Gaji Karyawan',
            'items' => array_values(array_filter([
                $canView('view karyawan') ? ['route' => 'karyawan.index', 'label' => 'Data Karyawan', 'icon' => 'mdi:account-group'] : null,
                $canView('view gaji') ? ['route' => 'gaji.index', 'label' => 'Transaksi Gaji', 'icon' => 'mdi:cash-multiple'] : null,
                $canView('view minus') ? ['route' => 'minus.index', 'label' => 'Laporan Minus', 'icon' => 'mdi:alert-circle-outline'] : null,
            ])),
        ],
        [
            'section' => 'Data Master',
            'items' => array_values(array_filter([
                $canView('view import shopee') ? ['route' => 'import.index', 'label' => 'Import Shopee', 'icon' => 'mdi:import'] : null,
                $canView('view import tiktok') ? ['route' => 'tiktok.import', 'label' => 'Import TikTok', 'icon' => 'mdi:music-note'] : null,
                $canView('view import brilink') ? ['route' => 'brilink.import', 'label' => 'Import BRILink', 'icon' => 'mdi:bank'] : null,
                $canView('view products') ? ['route' => 'products.index', 'label' => 'Produk', 'icon' => 'mdi:package-variant-closed'] : null,
                $canView('view stores') ? ['route' => 'stores.index', 'label' => 'Toko', 'icon' => 'mdi:store-outline'] : null,
            ])),
        ],
    ];

    // Hapus group kosong
    $navItems = array_values(array_filter($navItems, fn($g) => !empty($g['items'])));
@endphp

<div class="flex min-h-screen relative">

    {{-- ============ OVERLAY ============ --}}
    <div id="sidebarOverlay" 
         class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity lg:hidden" 
         onclick="toggleSidebar()"></div>

    {{-- ============ SIDEBAR ============ --}}
    <aside id="sidebar" 
           class="w-64 bg-white border-r border-gray-200 flex flex-col fixed h-screen z-50 transition-transform duration-300 transform -translate-x-full lg:translate-x-0">

        {{-- Logo / Brand --}}
        <div class="h-16 flex items-center justify-between px-6 border-b border-gray-200">
            <a href="/" class="flex items-center gap-2 font-bold text-lg text-gray-800">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm shadow-blue-500/30">
                    <iconify-icon icon="mdi:book-open-variant" class="text-white text-lg"></iconify-icon>
                </div>
                BukuBarokah
            </a>
            <button class="lg:hidden text-gray-500 hover:text-gray-700" onclick="toggleSidebar()">
                <iconify-icon icon="mdi:close" class="text-2xl"></iconify-icon>
            </button>
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

                            if ($item['route'] === 'zakat.index' && request()->routeIs('laporan-zakat.*')) {
                                $isActive = true;
                            }

                            $hasSubmenu = isset($item['submenu']);
                            $isSubmenuActive = $hasSubmenu && collect($item['submenu'])->contains(fn($sub) => request()->routeIs($sub['route']));
                            if ($isSubmenuActive) $isActive = true;
                        @endphp

                        {{-- ✅ JIKA PUNYA SUBMENU, UBAH JADI BUTTON --}}
                        @if($hasSubmenu)
                            <button type="button"
                                onclick="toggleSubmenu('submenu-{{ Str::slug($item['label']) }}')"
                                class="w-full flex items-center justify-between px-6 py-2.5 text-sm transition
                                        {{ $isActive
                                            ? 'bg-blue-50 text-blue-700 font-medium border-r-2 border-blue-600'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <div class="flex items-center gap-3">
                                    <iconify-icon icon="{{ $item['icon'] }}" class="text-lg {{ $isActive ? 'text-blue-600' : 'text-gray-400' }}"></iconify-icon>
                                    {{ $item['label'] }}
                                </div>
                                {{-- Ikon Panah berputar --}}
                                <iconify-icon icon="mdi:chevron-down"
                                    class="submenu-toggle-icon text-sm transition-transform duration-300 ease-in-out text-gray-400">
                                </iconify-icon>
                            </button>
                        @else
                            {{-- JIKA TIDAK PUNYA SUBMENU, TETAP JADI LINK --}}
                            <a href="{{ route($item['route']) }}"
                                @if($isActive) data-sidebar-active="true" @endif
                                class="flex items-center justify-between px-6 py-2.5 text-sm transition
                                        {{ $isActive
                                            ? 'bg-blue-50 text-blue-700 font-medium border-r-2 border-blue-600'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <div class="flex items-center gap-3">
                                    <iconify-icon icon="{{ $item['icon'] }}" class="text-lg {{ $isActive ? 'text-blue-600' : 'text-gray-400' }}"></iconify-icon>
                                    {{ $item['label'] }}
                                </div>
                            </a>
                        @endif

                        {{-- ✅ RENDER SUBMENU DENGAN ANIMASI --}}
                        @if($hasSubmenu)
                            <div id="submenu-{{ Str::slug($item['label']) }}" 
                                 class="overflow-hidden transition-all duration-300 ease-in-out bg-gray-50/50 border-r-2 border-transparent
                                        {{ $isSubmenuActive ? 'max-h-96 opacity-100 py-1' : 'max-h-0 opacity-0 py-0' }}">
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

        {{-- Footer Sidebar --}}
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
                v1.0 · BukuBarokah
            </div>
        </div>
    </aside>

    {{-- ============ MAIN CONTENT ============ --}}
    <div class="flex-1 w-full min-w-0 min-h-screen transition-all duration-300 ml-0 lg:ml-64 flex flex-col">

        {{-- Topbar (Sticky) --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-30 w-full">
            <div class="flex items-center gap-3">
                <button class="lg:hidden p-2 -ml-2 text-gray-600 hover:bg-gray-100 rounded-lg transition" onclick="toggleSidebar()">
                    <iconify-icon icon="mdi:menu" class="text-2xl"></iconify-icon>
                </button>
                <h1 class="text-lg font-semibold text-gray-800 truncate">
                    @yield('title', 'Pembukuan')
                </h1>
            </div>

            {{-- User Dropdown --}}
            <div class="relative" id="userDropdownContainer">
                <button type="button" onclick="toggleUserDropdown()"
                        class="flex items-center gap-3 pl-2 pr-3 py-1.5 rounded-lg hover:bg-gray-50 transition group">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-sm shadow-blue-500/30 flex-shrink-0">
                        <iconify-icon icon="mdi:account" class="text-white text-lg"></iconify-icon>
                    </div>
                    <div class="text-left hidden sm:block">
                        <div class="text-sm font-semibold text-gray-800 leading-tight">
                            {{ Auth::user()->name ?? 'Admin' }}
                        </div>
                        <div class="text-[10px] text-gray-400 leading-tight truncate max-w-[140px]">
                            {{ Auth::user()->email ?? '' }}
                        </div>
                    </div>
                    <iconify-icon icon="mdi:chevron-down" class="text-gray-400 text-base transition-transform group-hover:text-gray-600"></iconify-icon>
                </button>

                {{-- Dropdown Menu --}}
                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
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

                    <div class="py-1">
                        <div class="px-4 py-2 flex items-center gap-2 text-xs text-gray-500">
                            <iconify-icon icon="mdi:calendar" class="text-sm"></iconify-icon>
                            Login sejak: {{ now()->format('d M Y') }}
                        </div>
                        <button type="button" onclick="openChangePasswordModal()"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition">
                            <iconify-icon icon="mdi:lock-reset" class="text-base text-gray-500"></iconify-icon>
                            Ganti Password
                        </button>
                    </div>

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
        <main class="p-4 sm:p-6 lg:p-8 flex-1 w-full overflow-x-hidden">
            @yield('content')
        </main>
    </div>
</div>

{{-- MODAL GANTI PASSWORD --}}
<div id="changePasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeChangePasswordModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:lock-reset" class="text-blue-600 text-lg"></iconify-icon>
                <h3 class="font-bold text-gray-800">Ganti Password</h3>
            </div>
            <button type="button" onclick="closeChangePasswordModal()" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="mdi:close" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="changePasswordForm" onsubmit="submitChangePassword(event)" class="overflow-y-auto flex-1 flex flex-col">
            @csrf
            <div class="p-5 space-y-4">
                <div class="hidden bg-red-50 text-red-600 p-3 rounded text-sm" id="modalErrorChangePassword"></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password Lama <span class="text-red-500">*</span></label>
                    <input type="password" name="current_password" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password Baru <span class="text-red-500">*</span></label>
                    <input type="password" name="new_password" required minlength="8"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ulangi Password Baru <span class="text-red-500">*</span></label>
                    <input type="password" name="new_password_confirmation" required minlength="8"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeChangePasswordModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" id="btnSubmitChangePassword" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save" class="text-lg"></iconify-icon> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // ==================== SIDEBAR RESPONSIVE ====================
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('-translate-x-full');
        sidebar.classList.toggle('translate-x-0');

        if (overlay.classList.contains('hidden')) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10); 
        } else {
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }

    // ==================== USER DROPDOWN ====================
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        const container = document.getElementById('userDropdownContainer');
        const dropdown = document.getElementById('userDropdown');
        if (container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.getElementById('userDropdown').classList.add('hidden');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar.classList.contains('translate-x-0') && window.innerWidth < 1024) {
                toggleSidebar();
            }
        }
    });
</script>

<script>
    // ==================== TOGGLE SUBMENU DENGAN ANIMASI ====================
    function toggleSubmenu(id) {
        const submenu = document.getElementById(id);
        if (!submenu) return;

        const button = submenu.previousElementSibling;
        const icon = button.querySelector('.submenu-toggle-icon');
        const isClosed = submenu.classList.contains('max-h-0');

        if (isClosed) {
            submenu.classList.remove('max-h-0', 'opacity-0', 'py-0');
            submenu.classList.add('max-h-96', 'opacity-100', 'py-1');
            if (icon) icon.classList.add('rotate-180', 'text-blue-600');
        } else {
            submenu.classList.remove('max-h-96', 'opacity-100', 'py-1');
            submenu.classList.add('max-h-0', 'opacity-0', 'py-0');
            if (icon) icon.classList.remove('rotate-180', 'text-blue-600');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Cari semua submenu yang sedang terbuka (max-h-96)
        const openSubmenus = document.querySelectorAll('.max-h-96');
        openSubmenus.forEach(submenu => {
            const button = submenu.previousElementSibling;
            const icon = button.querySelector('.submenu-toggle-icon');
            if (icon) {
                icon.classList.add('rotate-180', 'text-blue-600');
            }
        });
    });
</script>

<script>
    // ==================== AUTO SCROLL KE MENU AKTIF ====================
    document.addEventListener('DOMContentLoaded', function () {
        const activeMenu = document.querySelector('[data-sidebar-active="true"]');
        const navContainer = document.querySelector('aside nav');

        if (activeMenu && navContainer) {
            setTimeout(() => {
                activeMenu.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            }, 100);
        }
    });
</script>

<script>
    // ==================== CHANGE PASSWORD ====================
    function openChangePasswordModal() {
        document.getElementById('changePasswordForm').reset();
        document.getElementById('modalErrorChangePassword').classList.add('hidden');
        document.getElementById('userDropdown').classList.add('hidden');
        document.getElementById('changePasswordModal').classList.remove('hidden');
        document.getElementById('changePasswordModal').classList.add('flex');
    }

    function closeChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.add('hidden');
        document.getElementById('changePasswordModal').classList.remove('flex');
    }

    async function submitChangePassword(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitChangePassword');
        const errDiv = document.getElementById('modalErrorChangePassword');
        const orig = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        errDiv.classList.add('hidden');

        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        try {
            const res = await fetch("{{ route('profile.change-password') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) {
                const msg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Gagal.');
                throw new Error(msg);
            }
            alert(data.message);
            closeChangePasswordModal();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    }
</script>
</body>
</html>