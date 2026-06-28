<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Pertanian Presisi Padi</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            color: #17221b;
            background: #eef4ef;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-shell {
            width: min(100%, 420px);
        }

        .brand {
            margin-bottom: 18px;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: #1f7a4d;
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 800;
            margin-bottom: 16px;
        }

        h1 {
            font-size: 28px;
            line-height: 1.15;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #607067;
            font-size: 15px;
        }

        .panel {
            background: #fff;
            border: 1px solid #dce7df;
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(35, 64, 48, 0.12);
            padding: 28px;
        }

        .alert {
            background: #fff4f2;
            border: 1px solid #f2c8bf;
            color: #9b2f1f;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #2b3a31;
            font-size: 14px;
            font-weight: 700;
        }

        input {
            width: 100%;
            height: 44px;
            border: 1px solid #cfdcd4;
            border-radius: 6px;
            padding: 0 12px;
            font-size: 15px;
            color: #17221b;
            background: #fbfdfb;
        }

        input:focus {
            outline: 3px solid rgba(31, 122, 77, 0.14);
            border-color: #1f7a4d;
            background: #fff;
        }

        .error-message {
            color: #b13a2b;
            font-size: 13px;
            margin-top: 6px;
        }

        button {
            width: 100%;
            height: 46px;
            border: 0;
            border-radius: 6px;
            background: #1f7a4d;
            color: white;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
        }

        button:hover {
            background: #19663f;
        }

        .footer {
            margin-top: 18px;
            text-align: center;
            color: #607067;
            font-size: 14px;
        }

        .footer a {
            color: #1f7a4d;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 480px) {
            body {
                padding: 18px;
                align-items: start;
            }

            .panel {
                padding: 22px;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="brand" aria-label="Smart Farming Admin">
            <div class="brand-mark">DEV</div>
            <h1>Admin Pertanian Presisi Padi</h1>
            <p class="subtitle">Masuk untuk memperbarui data klimatologi.</p>
        </section>

        <section class="panel">
            @if ($errors->any())
                <div class="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        placeholder="Masukkan username"
                    >
                    @error('username')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                    >
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit">Masuk</button>
            </form>
        </section>

        <p class="footer">Kembali ke <a href="/">halaman utama</a></p>
    </main>
</body>
</html>
