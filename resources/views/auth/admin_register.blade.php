<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FairScore — Admin Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        .step-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .hidden-step { opacity: 0; pointer-events: none; position: absolute; transform: translateX(20px); }
        .active-step { opacity: 1; pointer-events: auto; position: relative; transform: translateX(0); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4" style="background:radial-gradient(ellipse at top,#0f2040 0%,#050c1a 70%); overflow-x: hidden;">

<div class="w-full max-w-md animate-fade-in-up">

    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl font-black mx-auto mb-4" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#050c1a;box-shadow:0 8px 32px rgba(245,158,11,0.35);">
            FS
        </div>
        <h1 class="text-3xl font-black tracking-tight" style="color:#f1f5f9;">Create Admin Account</h1>
        <p class="text-base mt-2" style="color:#475569;">Secure portal access for coordinators.</p>
    </div>

    <div class="glass-card p-8 overflow-hidden relative">
        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg text-sm flex items-center gap-2" style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2);">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.register.post') }}" id="register-form">
            @csrf
            
            {{-- STEP 1 --}}
            <div id="step-1" class="step-transition active-step w-full">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold" style="color:#e2e8f0;">Step 1: Basic Info</h2>
                    <span class="text-xs font-bold px-2 py-1 rounded bg-slate-800 text-slate-400">1 of 2</span>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus class="form-input">
                    </div>
                    <div>
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}" required class="form-input">
                    </div>
                </div>

                <button type="button" onclick="nextStep()" class="btn-primary w-full justify-center mt-8 py-3 text-base">
                    Next Step
                    <svg class="w-5 h-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>

            {{-- STEP 2 --}}
            <div id="step-2" class="step-transition hidden-step w-full">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold" style="color:#e2e8f0;">Step 2: Credentials</h2>
                    <span class="text-xs font-bold px-2 py-1 rounded bg-slate-800 text-slate-400">2 of 2</span>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required class="form-input">
                    </div>

                    <div>
                        <label for="password" class="form-label">Strong Password</label>
                        <input type="password" id="password" name="password" placeholder="At least 8 chars, letters & numbers" required class="form-input" oninput="validatePassword()">
                        <p id="password-error" class="text-xs mt-1.5 text-red-400 hidden">Password must contain at least 8 characters, including both letters and numbers.</p>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button type="button" onclick="prevStep()" class="btn-secondary py-3 px-4">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button type="submit" id="submit-btn" class="btn-primary flex-1 justify-center py-3 text-base" disabled style="opacity: 0.5; cursor: not-allowed;">
                        <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        DONE
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-white/5 text-center">
            <p class="text-sm text-slate-400">
                Already have an account? 
                <a href="{{ route('admin.login') }}" class="text-amber-500 hover:text-amber-400 font-bold underline transition-colors">Sign in here</a>
            </p>
        </div>
    </div>
</div>

<script>
    function nextStep() {
        const name = document.getElementById('name').value.trim();
        const address = document.getElementById('address').value.trim();
        
        if (!name || !address) {
            alert('Please fill out all fields in Step 1.');
            return;
        }

        document.getElementById('step-1').classList.replace('active-step', 'hidden-step');
        document.getElementById('step-2').classList.replace('hidden-step', 'active-step');
        document.getElementById('username').focus();
    }

    function prevStep() {
        document.getElementById('step-2').classList.replace('active-step', 'hidden-step');
        document.getElementById('step-1').classList.replace('hidden-step', 'active-step');
    }

    function validatePassword() {
        const pwd = document.getElementById('password').value;
        const btn = document.getElementById('submit-btn');
        const err = document.getElementById('password-error');
        
        // At least 8 chars, contains a letter, contains a number
        const isValid = pwd.length >= 8 && /[a-zA-Z]/.test(pwd) && /[0-9]/.test(pwd);
        
        if (isValid) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            err.classList.add('hidden');
        } else {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            if (pwd.length > 0) {
                err.classList.remove('hidden');
            } else {
                err.classList.add('hidden');
            }
        }
    }

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
