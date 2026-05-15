<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Smart-Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --white: #ffffff; --bg: #f5f6f8; --border: #e4e6ec; --border-2: #c8ccd8;
            --text-1: #0d1117; --text-2: #4b5263; --text-3: #8892a4;
            --blue: #2563eb; --blue-hover: #1d4ed8; --blue-light: #eff6ff;
            --red: #dc2626; --red-light: #fef2f2;
            --font: 'Plus Jakarta Sans', sans-serif;
        }
        body { font-family: var(--font); background: var(--bg); color: var(--text-1); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }

        .login-wrap { display: grid; grid-template-columns: 1fr 1fr; min-height: 560px; width: 100%; max-width: 900px; background: var(--white); border-radius: 20px; overflow: hidden; border: 1px solid var(--border); box-shadow: 0 20px 60px rgba(0,0,0,.08); }

        /* LEFT PANEL */
        .login-visual {
            background: linear-gradient(145deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
            padding: 48px 40px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;
        }
        .login-visual::before {
            content: ''; position: absolute; top: -60px; right: -60px; width: 280px; height: 280px;
            border: 1px solid rgba(255,255,255,.1); border-radius: 50%;
        }
        .login-visual::after {
            content: ''; position: absolute; bottom: -80px; left: -40px; width: 220px; height: 220px;
            border: 1px solid rgba(255,255,255,.08); border-radius: 50%;
        }
        .visual-logo { display: flex; align-items: center; gap: 10px; }
        .visual-logo-icon { width: 36px; height: 36px; background: rgba(255,255,255,.2); border-radius: 9px; display: flex; align-items: center; justify-content: center; }
        .visual-logo-icon i { color: #fff; font-size: 18px; }
        .visual-logo-text { font-size: 16px; font-weight: 700; color: #fff; }
        .visual-headline { color: #fff; font-size: 26px; font-weight: 700; line-height: 1.3; letter-spacing: -.5px; }
        .visual-headline span { opacity: .6; }
        .visual-stats { display: flex; gap: 24px; }
        .visual-stat { }
        .visual-stat-num { font-size: 22px; font-weight: 700; color: #fff; }
        .visual-stat-label { font-size: 12px; color: rgba(255,255,255,.6); margin-top: 2px; }

        /* RIGHT PANEL */
        .login-form { padding: 48px 40px; display: flex; flex-direction: column; justify-content: center; }
        .form-header { margin-bottom: 32px; }
        .form-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -.4px; }
        .form-header p { font-size: 14px; color: var(--text-2); margin-top: 6px; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-1); margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-3); font-size: 17px; }
        .form-control {
            width: 100%; padding: 11px 13px 11px 40px; border: 1px solid var(--border-2);
            border-radius: 9px; font-size: 14px; font-family: var(--font); color: var(--text-1);
            background: var(--white); outline: none; transition: border-color .18s, box-shadow .18s;
        }
        .form-control:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .form-control.error { border-color: var(--red); }
        .form-error { font-size: 12px; color: var(--red); margin-top: 5px; display: flex; align-items: center; gap: 4px; }

        .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-3); cursor: pointer; font-size: 17px; border: none; background: none; }

        .btn-login {
            width: 100%; padding: 12px; background: var(--blue); color: #fff; border: none;
            border-radius: 9px; font-size: 14px; font-weight: 700; font-family: var(--font);
            cursor: pointer; transition: background .18s, transform .15s, box-shadow .18s; margin-top: 6px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { background: var(--blue-hover); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.3); }
        .btn-login:active { transform: translateY(0); }

        .alert-error {
            background: var(--red-light); color: #991b1b; border: 1px solid #fecaca;
            padding: 11px 14px; border-radius: 9px; font-size: 13.5px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }

        .demo-section { margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border); }
        .demo-title { font-size: 12px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 10px; }
        .demo-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .demo-card {
            padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer;
            transition: all .18s; background: var(--bg);
        }
        .demo-card:hover { border-color: var(--blue); background: var(--blue-light); }
        .demo-card-role { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text-3); }
        .demo-card-email { font-size: 12.5px; color: var(--text-1); font-weight: 500; margin-top: 2px; }

        @media (max-width: 640px) {
            .login-wrap { grid-template-columns: 1fr; max-width: 420px; }
            .login-visual { display: none; }
            .login-form { padding: 36px 28px; }
        }
    </style>
</head>
<body>

<div class="login-wrap">
    {{-- LEFT VISUAL --}}
    <div class="login-visual">
        <div class="visual-logo">
            <div class="visual-logo-icon"><i class="ti ti-building-community"></i></div>
            <div class="visual-logo-text">Smart-Hub</div>
        </div>
        <div>
            <div class="visual-headline">
                Kelola ruang &<br><span>peralatan studio</span><br>lebih cerdas.
            </div>
        </div>
        <div class="visual-stats">
            <div class="visual-stat">
                <div class="visual-stat-num">4</div>
                <div class="visual-stat-label">Ruangan Aktif</div>
            </div>
            <div class="visual-stat">
                <div class="visual-stat-num">12+</div>
                <div class="visual-stat-label">Peralatan</div>
            </div>
            <div class="visual-stat">
                <div class="visual-stat-num">24/7</div>
                <div class="visual-stat-label">Akses Sistem</div>
            </div>
        </div>
    </div>

    {{-- RIGHT FORM --}}
    <div class="login-form">
        <div class="form-header">
            <h1>Selamat datang</h1>
            <p>Masuk untuk mengakses Smart-Hub Management System</p>
        </div>

        @if($errors->any())
            <div class="alert-error"><i class="ti ti-alert-circle"></i> {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <div class="input-wrap">
                    <i class="ti ti-mail input-icon"></i>
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'error' : '' }}"
                           placeholder="nama@email.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <i class="ti ti-lock input-icon"></i>
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="toggle-pw" onclick="togglePw()">
                        <i class="ti ti-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="ti ti-login"></i> Masuk ke Sistem
            </button>
        </form>

        <div class="demo-section">
            <div class="demo-title">Login Akun</div>
            <div class="demo-cards">
                <div class="demo-card" onclick="fillDemo('411253003@undira.ac.id','password')">
                    <div class="demo-card-role">Admin</div>
                    <div class="demo-card-email">411253003@undira.ac.id</div>
                </div>
                <div class="demo-card" onclick="fillDemo('rangga@gmail.com','password')">
                    <div class="demo-card-role">Member</div>
                    <div class="demo-card-email">rangga@gmail.com</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw() {
    const inp = document.getElementById('passwordInput');
    const ico = document.getElementById('eyeIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'ti ti-eye' : 'ti ti-eye-off';
}
function fillDemo(email, pw) {
    document.querySelector('[name=email]').value = email;
    document.querySelector('[name=password]').value = pw;
}
</script>
</body>
</html>
