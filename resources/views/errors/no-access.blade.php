<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidak Ada Akses</title>
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
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <main class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
            <iconify-icon icon="mdi:lock-alert-outline" class="text-amber-600 text-4xl"></iconify-icon>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">Tidak Ada Akses</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">
            Akun Anda belum memiliki izin untuk membuka halaman mana pun.
            Silakan hubungi administrator untuk meminta akses.
        </p>
        <button type="button" onclick="window.history.back()" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
            <iconify-icon icon="mdi:arrow-left" class="text-lg"></iconify-icon>
            Kembali
        </button>
    </main>
</body>
</html>
