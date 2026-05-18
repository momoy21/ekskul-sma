<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login- Sistem Ekstrakurikuler SMA Global Indonesia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0F2544 0%, #0a1628 50%, #0c2340 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
            overflow: hidden;
            position: relative;
        }

        body::before {
        content: '';
        position: fixed;
        width: 350px; height: 350px; border-radius: 50%;
        background: radial-gradient(circle, rgba(0,201,167,0.2) 0%, transparent 70%);
        top: -80px; right: -80px; pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            width: 280px; height: 280px; border-radius: 50%;
            background: radial-gradient(circle, rgba(45,125,210,0.18) 0%, transparent 70%);
            bottom: -60px; left: -50px; pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="bg-dots" aria-hidden="true"></div>

    <div class="login-card">
        <div class="login-brand">
            <i class="bi bi-mortarboard-fill school-icon"></i>
            <h5>SISTEM EKSTRAKURIKULER<br>SMA GLOBAL INDONESIA</h5>
            <small>Masuk dengan akun yang diberikan guru koordinator</small>
        </div>

        {{-- Flash messages --}}
        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <small>{{ session('error') }}</small>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <small>{{ session('success') }}</small>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            {{-- Username / NISN --}}
            <div class="mb-3">
                <label class="form-label">Username atau NISN</label>
                <input
                    type="text"
                    name="username"
                    class="form-control @error('username') is-invalid @enderror"
                    placeholder="Masukkan username atau NISN"
                    value="{{ old('username') }}"
                    maxlength="10"
                    autofocus
                    autocomplete="username"
                >
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input
                        type="password"
                        id="passwordInput"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                    >
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        onclick="togglePassword()"
                        tabindex="-1"
                    >
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1.5"></i> Login
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const input    = document.getElementById('passwordInput');
            const icon     = document.getElementById('eyeIcon');
            const isHidden = input.type === 'password';
            input.type     = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        }
    </script>
</body>
</html>
