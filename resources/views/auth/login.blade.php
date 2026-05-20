<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pengaturan->nama_website ?? 'SIPERPUS' }} - Login</title>

    <!-- Anti-FOUC: apply saved theme before render -->
    <script>
        (function(){
            var t = localStorage.getItem('theme');
            if (t === 'dark') document.documentElement.setAttribute('data-theme','dark');
        })();
    </script>

    @if($pengaturan && $pengaturan->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset($pengaturan->favicon) }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ── Base ── */
        html[data-theme="dark"] body {
            background-color: #0f172a;
            color: #e2e8f0;
        }

        /* ── Card ── */
        html[data-theme="dark"] .login-card {
            background-color: #1e293b;
            border-color: #334155;
        }

        /* ── Inputs ── */
        html[data-theme="dark"] .login-input {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .login-input::placeholder {
            color: #475569;
        }
        html[data-theme="dark"] .login-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
            outline: none;
        }

        /* ── Labels & text ── */
        html[data-theme="dark"] .login-label  { color: #94a3b8; }
        html[data-theme="dark"] .login-title  { color: #f1f5f9; }
        html[data-theme="dark"] .login-footer { color: #475569; }
        html[data-theme="dark"] .login-divider { border-color: #334155; }

        /* ── Toggle button ── */
        .theme-toggle-btn {
            position: fixed; top: 1rem; right: 1rem;
            width: 2.25rem; height: 2.25rem;
            border-radius: 0.625rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: 1px solid;
            transition: all 0.2s ease;
        }
        html:not([data-theme="dark"]) .theme-toggle-btn {
            background: #fff;
            border-color: #e2e8f0;
            color: #475569;
        }
        html:not([data-theme="dark"]) .theme-toggle-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        html[data-theme="dark"] .theme-toggle-btn {
            background: #1e293b;
            border-color: #334155;
            color: #94a3b8;
        }
        html[data-theme="dark"] .theme-toggle-btn:hover {
            background: #334155;
            border-color: #475569;
            color: #cbd5e1;
        }
        .icon-moon { display: block; }
        .icon-sun  { display: none; }
        html[data-theme="dark"] .icon-moon { display: none; }
        html[data-theme="dark"] .icon-sun  { display: block; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center transition-colors duration-200">

    <!-- Dark Mode Toggle -->
    <button id="loginThemeToggle" class="theme-toggle-btn" title="Toggle Dark Mode">
        <i class="fas fa-moon icon-moon text-sm"></i>
        <i class="fas fa-sun icon-sun text-sm"></i>
    </button>

    <div class="login-card bg-white rounded-2xl shadow-xl border border-gray-100 p-8 w-full max-w-md mx-4 transition-colors duration-200">

        <!-- Logo & Title -->
        <div class="text-center mb-8">
            @if($pengaturan && $pengaturan->logo)
                <img src="{{ asset($pengaturan->logo) }}" alt="Logo"
                     class="h-16 w-auto mx-auto mb-4 object-contain">
            @else
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book-open text-indigo-600 text-2xl"></i>
                </div>
            @endif
            <h1 class="login-title text-2xl font-bold text-gray-800">
                {{ $pengaturan->nama_website ?? 'SIPERPUS' }}
            </h1>
            @if($pengaturan && $pengaturan->deskripsi_website)
            <p class="login-label text-xs text-gray-500 mt-1">{{ $pengaturan->deskripsi_website }}</p>
            @endif
        </div>

        <!-- Error -->
        @if($errors->any())
        <div class="flex items-start gap-2.5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm mb-5"
             style="html[data-theme='dark'] { background: rgba(239,68,68,0.1); }">
            <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
            <div>
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="login-label block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-1.5 text-indigo-400 text-xs"></i>Email
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="login-input w-full px-4 py-3 border border-gray-300 rounded-xl text-sm
                              focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all"
                       placeholder="Masukkan email Anda" required autofocus>
            </div>

            <div>
                <label for="password" class="login-label block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock mr-1.5 text-indigo-400 text-xs"></i>Password
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password"
                           class="login-input w-full px-4 py-3 pr-11 border border-gray-300 rounded-xl text-sm
                                  focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all"
                           placeholder="Masukkan password Anda" required>
                    <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 px-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-eye text-sm" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember"
                       class="w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer accent-indigo-500">
                <label for="remember" class="login-label ml-2 text-sm text-gray-600 cursor-pointer">
                    Ingat saya
                </label>
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700
                           text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200
                           shadow-md hover:shadow-lg hover:shadow-indigo-500/30 active:scale-[.98]">
                <i class="fas fa-sign-in-alt mr-2"></i>Masuk
            </button>
        </form>

        <!-- Footer -->
        <div class="login-divider text-center mt-6 pt-5 border-t border-gray-200">
            <p class="login-footer text-xs text-gray-400">
                &copy; {{ date('Y') }} {{ $pengaturan->deskripsi_website ?? ($pengaturan->nama_website ?? 'SIPERPUS') }}
            </p>
            <p class="login-footer text-xs text-gray-400 mt-1">
                by <a href="https://www.instagram.com/rinomakin" class="text-indigo-400 hover:text-indigo-500 transition-colors">@rinomakin</a>
            </p>
        </div>
    </div>

    <script>
    (function () {
        var btn  = document.getElementById('loginThemeToggle');
        var html = document.documentElement;

        btn.addEventListener('click', function () {
            var current = html.getAttribute('data-theme') || 'light';
            var next    = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });

        // Password toggle
        var pwBtn  = document.getElementById('togglePassword');
        var pwInput = document.getElementById('password');
        var pwIcon  = document.getElementById('pwIcon');
        pwBtn.addEventListener('click', function () {
            var isText = pwInput.type === 'text';
            pwInput.type = isText ? 'password' : 'text';
            pwIcon.className = isText ? 'fas fa-eye text-sm' : 'fas fa-eye-slash text-sm';
        });
    })();
    </script>
</body>
</html>
