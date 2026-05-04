@extends('layouts.judge')

@section('title', 'Dashboard')

@section('content')

{{-- Welcome Hero Section (Visible initially) --}}
<div id="hero-section" class="flex flex-col items-center justify-center min-h-[60vh] text-center transition-all duration-700 ease-out">
    <div class="w-20 h-20 bg-amber-500/10 rounded-full flex items-center justify-center mb-6 shadow-xl shadow-amber-900/20">
        <svg class="w-10 h-10 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
        </svg>
    </div>
    <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white mb-3">Ready to Score?</h1>
    <p class="text-slate-400 mt-2 max-w-md mx-auto leading-relaxed">
        The system is connected to the tabulation engine. When you're ready, click <strong class="text-amber-500">START</strong> in the header to view available events.
    </p>
</div>

{{-- Events List (Hidden initially) --}}
<div id="events-section" class="hidden opacity-0 translate-y-8 transition-all duration-500 ease-out"> 
    <div class="mb-8 border-b border-white/5 pb-4">
        <h2 class="text-2xl font-black text-white px-2">Assigned Events</h2>
    </div>

    @if($events->isEmpty())
        <div class="bg-[#0b1426]/50 border border-white/5 rounded-2xl p-12 text-center text-slate-400 backdrop-blur-sm">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p>No events have been assigned to you yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
                <div class="group relative bg-[#0b1426]/80 backdrop-blur-md rounded-2xl border border-white/5 p-6 hover:border-amber-500/50 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-xl shadow-inner border border-white/5">
                            {{ $event->status === 'live' ? '🔴' : '📅' }}
                        </div>
                        @if($event->status === 'live')
                            <span class="bg-red-500/10 text-red-400 text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border border-red-500/20 animate-pulse">Live Now</span>
                        @elseif($event->status === 'completed')
                            <span class="bg-slate-800 text-slate-400 text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border border-slate-700">Ended</span>
                        @else
                            <span class="bg-blue-500/10 text-blue-400 text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border border-blue-500/20">Upcoming</span>
                        @endif
                    </div>

                    <h3 class="text-xl font-bold text-slate-100 mb-2 truncate">{{ $event->name }}</h3>
                    <p class="text-sm text-slate-500 line-clamp-2 h-10 mb-4">{{ $event->description ?? 'No description provided.' }}</p>

                    <div class="flex items-center gap-4 text-xs font-medium text-slate-400 mb-6">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $event->participants_count }} Contestants
                        </div>
                    </div>

                    @if($event->status === 'live')
                        <a href="{{ route('judge.score', $event) }}" class="w-full inline-flex justify-center items-center gap-2 bg-amber-600 hover:bg-amber-500 text-white font-bold px-4 py-2.5 rounded-xl transition-all shadow-md">
                            Evaluate
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                    @else
                        <button disabled class="w-full inline-flex justify-center items-center gap-2 bg-slate-800 text-slate-500 font-bold px-4 py-2.5 rounded-xl transition-all cursor-not-allowed">
                            {{ $event->status === 'completed' ? 'Scoring Closed' : 'Waiting for Admin...' }}
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const shouldStart = urlParams.get('start') === '1';
        
        const heroSection = document.getElementById('hero-section');
        const eventsSection = document.getElementById('events-section');
        const globalStartBtn = document.getElementById('global-start-btn');

        function triggerDashboardStart(e) {
            if(e) e.preventDefault();
            
            // Fade out hero
            heroSection.style.opacity = '0';
            heroSection.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                heroSection.classList.add('hidden');
                
                // Show events
                eventsSection.classList.remove('hidden');
                // Frame delay for CSS transition
                requestAnimationFrame(() => {
                    eventsSection.classList.remove('opacity-0', 'translate-y-8');
                });
                
            }, 500); // Wait for hero to fade out
        }

        // Add event listener to the START button in the header
        if (globalStartBtn) {
            globalStartBtn.addEventListener('click', (e) => {
                // If we are already on the dashboard, just do the JS transition
                if (window.location.pathname === '{{ route('judge.dashboard', [], false) }}' 
                    || window.location.pathname === '/judge/dashboard') {
                    triggerDashboardStart(e);
                    
                    // Optional: update URL silently so reloading keeps the state
                    const newUrl = new URL(window.location);
                    newUrl.searchParams.set('start', '1');
                    window.history.pushState({}, '', newUrl);
                }
            });
        }

        // If returned to dashboard with ?start=1, auto-trigger
        if (shouldStart) {
            triggerDashboardStart();
        }
    });
</script>
@endpush
@endsection
