<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FairScore — Judge Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        .admin-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.25); border-radius: 9999px; font-size: 0.65rem; font-weight: 700; color: #818cf8; text-transform: uppercase; letter-spacing: 0.08em; margin-left: 0.5rem; vertical-align: middle; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4" style="background:radial-gradient(ellipse at top,#0f2040 0%,#050c1a 70%);">

<div class="w-full max-w-sm animate-fade-in-up">

    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl font-black mx-auto mb-4 border border-indigo-500/20 shadow-inner" style="background:rgba(99,102,241,0.1);color:#818cf8;">
            FS
        </div>
        <h1 class="text-3xl font-black tracking-tight" style="color:#f1f5f9;">FairScore</h1>
        <p class="text-base mt-2" style="color:#475569;">Judge Authentication Portal</p>
    </div>

    <div class="glass-card p-8" style="border-color: rgba(99,102,241,0.15);">

        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg text-sm flex items-center gap-2" style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2);">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ $errors->first('username') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2);">
                {{ session('error') }}
            </div>
        @endif

        <h2 class="text-base font-bold mb-4" style="color:#e2e8f0;">
            Sign In
            <span class="admin-badge">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                Judge
            </span>
        </h2>

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <input type="hidden" name="intended_role" value="judge">
        <div class="space-y-4">
            <div>
                <label for="judge-username" class="form-label">Username</label>
                    <input type="text" id="judge-username" name="username" value="{{ old('username') }}" autocomplete="username" placeholder="Enter judge username" required autofocus class="form-input focus:border-indigo-500 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label for="judge-password" class="form-label">Password</label>
                    <input type="password" id="judge-password" name="password" autocomplete="current-password" placeholder="Enter your password" required class="form-input focus:border-indigo-500 focus:ring-indigo-500/20">
                </div>
            </div>

            <button type="submit" class="btn-primary w-full justify-center mt-6 py-3 text-base" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;box-shadow:0 4px 20px rgba(99,102,241,0.4);">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                Sign In as Judge
            </button>
        </form>

        <p class="text-center text-sm mt-6 pt-5 border-t border-white/5" style="color:#475569;">
            Contact your administrator if you need access.
        </p>
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
