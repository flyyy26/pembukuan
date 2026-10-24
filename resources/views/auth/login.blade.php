<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin · BukuProfit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap');
        * { font-family: 'Geist', sans-serif; }
        iconify-icon { display: inline-block; vertical-align: middle; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-500 rounded-2xl shadow-lg shadow-blue-500/30 mb-4">
                <iconify-icon icon="mdi:book-open-variant" class="text-white text-3xl"></iconify-icon>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">BukuProfit</h1>
            <p class="text-sm text-gray-500 mt-1">Login untuk mengakses dashboard</p>
        </div>

        {{-- Card Login --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 p-8">

            {{-- Error --}}
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-5 flex items-start gap-2">
                    <iconify-icon icon="mdi:alert-circle" class="text-red-600 text-lg mt-0.5"></iconify-icon>
                    <div class="text-sm">{{ session('error') }}</div>
                </div>
            @endif

            {{-- Validasi --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-5">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tambahkan id="loginForm" pada form --}}
            <form method="POST" action="{{ route('login.post') }}" class="space-y-5" id="loginForm">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:email-outline" class="text-gray-400 text-lg"></iconify-icon>
                        </div>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="Masukkan email"
                               required
                               autofocus
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-3 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                      transition">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <iconify-icon icon="mdi:lock-outline" class="text-gray-400 text-lg"></iconify-icon>
                        </div>
                        <input type="password"
                               name="password"
                               id="password"
                               placeholder="Masukkan password"
                               required
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-10 py-3 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                      transition">
                        <button type="button"
                                onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <iconify-icon id="eye-icon" icon="mdi:eye-outline" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
                </div>

                {{-- Submit (Tambahkan ID) --}}
                <button type="submit"
                        id="btnLogin"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition
                               font-medium shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2
                               disabled:opacity-70 disabled:cursor-not-allowed">
                    <iconify-icon id="btnLoginIcon" icon="mdi:login" class="text-lg"></iconify-icon>
                    <span id="btnLoginText">Masuk</span>
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-6 text-xs text-gray-400">
            © {{ date('Y') }} BukuProfit · Sistem Pembukuan
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('icon', 'mdi:eye-off-outline');
            } else {
                input.type = 'password';
                icon.setAttribute('icon', 'mdi:eye-outline');
            }
        }

        // ✅ TAMBAHAN: Efek Loading saat submit (baik via Enter maupun klik)
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            const btnText = document.getElementById('btnLoginText');
            const btnIcon = document.getElementById('btnLoginIcon');

            // Disable tombol agar tidak bisa diklik 2x
            btn.disabled = true;
            
            // Ubah teks dan icon menjadi loading
            btnText.textContent = 'Memproses...';
            btnIcon.setAttribute('icon', 'mdi:loading');
            btnIcon.classList.add('animate-spin');
        });
    </script>

</body>
</html>