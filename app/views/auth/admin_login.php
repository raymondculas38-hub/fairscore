<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FairScore — Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <?= vite(['resources/css/app.css']) ?>
    <style>
        .or-divider { display: flex; align-items: center; gap: 0.75rem; margin: 1rem 0; }
        .or-divider-line { flex: 1; height: 1px; background: rgba(203, 213, 225, 0.18); }
        .or-divider-text { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #4b5563; flex-shrink: 0; }
        .btn-google { width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.65rem; padding: 0.75rem 1rem; border-radius: 0.625rem; font-size: 0.875rem; font-weight: 600; color: #f8fafc; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); cursor: pointer; transition: all 0.2s ease; text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .btn-google:hover { background: rgba(255,255,255,0.09); border-color: rgba(255,255,255,0.2); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); color: #ffffff; }
        .admin-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.25); border-radius: 9999px; font-size: 0.65rem; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.08em; margin-left: 0.5rem; vertical-align: middle; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4" style="background:radial-gradient(ellipse at top,#0f2040 0%,#050c1a 70%);">

<div class="w-full max-w-sm animate-fade-in-up">

    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl font-black mx-auto mb-4" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#050c1a;box-shadow:0 8px 32px rgba(245,158,11,0.35);">
            FS
        </div>
        <h1 class="text-3xl font-black tracking-tight" style="color:#f1f5f9;">FairScore</h1>
        <p class="text-base mt-2" style="color:#475569;">Admin Authentication Portal</p>
    </div>

    <div class="glass-card p-8">

        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="mb-4 px-4 py-3 rounded-lg text-sm flex items-center gap-2" style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2);">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <?= htmlspecialchars((string)(implode(' ', $_SESSION['errors']))) ?>
            </div>
        <?php unset($_SESSION['errors']); endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2);">
                <?= htmlspecialchars((string)(session('error'))) ?>
            </div>
        <?php endif; ?>

        <h2 class="text-base font-bold mb-4" style="color:#e2e8f0;">
            Sign In
            <span class="admin-badge">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Admin
            </span>
        </h2>

    <form method="POST" action="<?= htmlspecialchars((string)(route('login.post'))) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="intended_role" value="admin">
        <div class="space-y-4">
            <div>
                <label for="admin-username" class="form-label">Username</label>
                    <input type="text" id="admin-username" name="username" value="<?= htmlspecialchars((string)(old('username'))) ?>" autocomplete="username" placeholder="Enter admin username" required autofocus class="form-input">
                </div>
                <div>
                    <label for="admin-password" class="form-label">Password</label>
                    <input type="password" id="admin-password" name="password" autocomplete="current-password" placeholder="Enter your password" required class="form-input">
                </div>
            </div>

            <button type="submit" class="btn-primary w-full justify-center mt-6 py-3 text-base">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                Sign In as Admin
            </button>
        </form>

        <div class="mt-6 text-center">
            <div class="or-divider" aria-hidden="true"><div class="or-divider-line"></div><span class="or-divider-text">OR</span><div class="or-divider-line"></div></div>
            <a href="<?= htmlspecialchars((string)(route('auth.google'))) ?>" class="btn-google">
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Continue with Google
            </a>

            <div class="mt-6 pt-5 border-t border-white/5 text-center">
                <p class="text-sm text-slate-400 mb-0">
                    Don't have an admin account? 
                    <a href="<?= htmlspecialchars((string)(route('admin.register'))) ?>" class="text-amber-500 hover:text-amber-400 font-bold underline transition-colors">Create account</a>
                </p>
            </div>
        </div>
    </div>
</div>
<script>
    // Delay password masking by 0.8 seconds (800ms)
    document.querySelectorAll('input[type="password"]').forEach(input => {
        let hideTimer;
        input.addEventListener('input', () => {
            input.type = 'text';
            clearTimeout(hideTimer);
            hideTimer = setTimeout(() => {
                input.type = 'password';
            }, 800);
        });
    });
</script>
</body>
</html>
