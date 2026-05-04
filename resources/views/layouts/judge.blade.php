<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('event_title', 'FairScore') }} — Judge Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    <style>
        body { 
            touch-action: manipulation;
            background: radial-gradient(circle at top right, #0d1e3a, #050c1a 60%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        .glass-header {
            background: rgba(7, 16, 31, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased overflow-x-hidden selection:bg-amber-500/30 selection:text-amber-200">

{{-- ── Topbar ───────────────────────────────────────────────────────── --}}
<header class="fixed top-0 left-0 right-0 w-full z-50 glass-header transition-all duration-300">
    <div class="w-full px-4 md:px-8">
        <div class="flex items-center justify-between w-full h-16">
            
            {{-- Branding --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-black shadow-lg shadow-amber-900/20" 
                     style="background:linear-gradient(135deg, {{ setting('theme_primary_color', '#f59e0b') }}, #d97706);color:#050c1a;">
                    FS
                </div>
                <div class="hidden sm:block">
                    <div class="font-bold text-sm tracking-wide" style="color:#f1f5f9;">{{ setting('event_title', 'FairScore') }}</div>
                    <div class="text-xs font-medium" style="color:#64748b;letter-spacing:0.02em;">JUDGE PORTAL</div>
                </div>
            </div>
            
            {{-- Actions & Context --}}
            <div class="flex items-center gap-2 sm:gap-4">
                
                {{-- Notifications --}}
                <div class="relative" id="notification-container">
                    <button id="notification-btn" class="relative p-2 rounded-xl bg-slate-800 border border-white/5 hover:border-amber-500/30 transition shadow-inner flex items-center justify-center text-slate-400 hover:text-amber-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-0 right-0 flex h-3 w-3 -mt-0.5 -mr-0.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border border-[#07101f]"></span>
                            </span>
                        @endif
                    </button>
                    {{-- Dropdown --}}
                    <div id="notification-dropdown" class="absolute right-0 mt-3 w-80 bg-slate-900 border border-slate-700 shadow-xl shadow-black/50 rounded-2xl overflow-hidden hidden" style="z-index: 100;">
                        <div class="px-4 py-3 border-b border-slate-700 bg-slate-800/50 flex justify-between items-center">
                            <span class="font-bold text-sm text-white">Notifications</span>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('judge.notifications.markAllRead') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-amber-500 hover:text-amber-400 font-semibold tracking-wider transition-colors">MARK ALL READ</button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-72 overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: #334155 #0f172a;">
                            @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                                <div class="px-4 py-3 border-b border-slate-800 hover:bg-slate-800/80 transition {{ $notification->read_at ? 'opacity-60' : 'bg-slate-800/40' }}">
                                    <div class="text-[10px] font-semibold text-amber-500 mb-0.5 uppercase tracking-wider">{{ $notification->data['event_name'] }}</div>
                                    <div class="text-xs text-slate-200 font-bold mb-1 leading-tight">{{ $notification->data['message'] }}</div>
                                    @if(!empty($notification->data['description']))
                                        <div class="text-[10px] text-slate-400 line-clamp-1 leading-relaxed">{{ $notification->data['description'] }}</div>
                                    @endif
                                    <div class="text-[9px] text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-center text-xs text-slate-500 font-medium">No recent notifications.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="hidden md:flex items-center mr-2">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse mr-2"></div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">{{ auth()->user()->name }}</span>
                </div>

                {{-- Global Event Bus Trigger for START --}}
                <a href="{{ route('judge.dashboard') }}?start=1" id="global-start-btn" 
                   class="bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs px-4 py-2 rounded-lg transition-all shadow-lg shadow-amber-900/20 flex items-center gap-2 cursor-pointer">
                   <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                   START
                </a>

                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0 flex">
                    @csrf
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs px-4 py-2 rounded-lg transition-all border border-slate-700 hover:border-slate-600 focus:ring-2 focus:ring-slate-500 outline-none flex items-center justify-center min-w-[75px]">
                        LOGOUT
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- Spacer to push content down below fixed header --}}
<div class="h-16"></div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 shadow-lg shadow-green-900/10 animate-fade-in-up" 
             style="background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.25);backdrop-filter:blur(8px);">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ session('success') }}
        </div>
    </div>
@endif

{{-- Page content --}}
<main class="@yield('container-class', 'max-w-7xl mx-auto') px-4 py-8">
    @yield('content')
</main>

{{-- Real-Time Reminder Modal (Hidden by default, very high z-index) --}}
<div id="reminder-modal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0 pointer-events-none">
    <div class="bg-red-600 rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 transform scale-95 transition-transform duration-300 border border-red-400 text-center flex flex-col items-center">
        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-white animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h2 class="text-3xl font-black text-white mb-2 uppercase tracking-wide">Reminder!</h2>
        <p class="text-red-100 font-medium text-lg leading-tight">
            Please submit your scores for <br/><strong id="reminder-event-name" class="text-white text-xl">this event</strong>.
        </p>
    </div>
</div>

@stack('scripts')
<script>
    const nBtn = document.getElementById('notification-btn');
    const nDrop = document.getElementById('notification-dropdown');
    if(nBtn && nDrop) {
        nBtn.addEventListener('click', (e) => { 
            e.stopPropagation(); 
            nDrop.classList.toggle('hidden'); 
        });
        document.addEventListener('click', (e) => { 
            if(!document.getElementById('notification-container').contains(e.target)) nDrop.classList.add('hidden'); 
        });
    }

    // --- Socket.IO Logic for Real-Time Reminders ---
    @auth
    const judgeId = {{ auth()->id() }};
    const socket = io('http://localhost:3000');
    
    socket.on(`judge-reminder-${judgeId}`, (data) => {
        const modal = document.getElementById('reminder-modal');
        const eventNameEl = document.getElementById('reminder-event-name');
        
        if (eventNameEl && data.event_name) {
            eventNameEl.textContent = data.event_name;
        }

        // Show Modal
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        modal.querySelector('div').classList.remove('scale-95');
        modal.querySelector('div').classList.add('scale-100');

        // Auto-dismiss after 4 seconds
        setTimeout(() => {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('div').classList.remove('scale-100');
            modal.querySelector('div').classList.add('scale-95');
        }, 4000);
    });
    @endauth
</script>
</body>
</html>
