@extends('layouts.judge')

@section('title', 'Event Verification')

@section('content')

<div class="min-h-[60vh] flex flex-col items-center justify-center animate-fade-in-up">
    <div class="bg-[#0b1426]/80 backdrop-blur-md rounded-2xl border border-white/5 p-8 max-w-md w-full shadow-2xl">
        <div class="w-16 h-16 bg-red-500/10 rounded-2xl mx-auto flex items-center justify-center mb-6 shadow-inner border border-red-500/20">
            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        
        <h2 class="text-2xl font-black text-white text-center mb-2">Event Locked</h2>
        <p class="text-slate-400 text-center text-sm mb-6">Please enter the 6-digit PIN for <strong>{{ $event->name }}</strong>. Check your notifications for the PIN.</p>

        <form method="POST" action="{{ route('judge.score.verify', $event) }}">
            @csrf
            
            <div class="mb-6">
                <input type="text" name="pin" maxlength="6" required autofocus
                       class="w-full bg-[#050c1a] border @error('pin') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-4 text-center text-2xl font-mono tracking-[0.5em] text-white focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all placeholder:text-slate-600 placeholder:tracking-normal" 
                       placeholder="XXXXXX" autocomplete="off">
                @error('pin')
                    <p class="text-red-400 text-xs mt-2 text-center font-semibold animate-pulse">{{ $message }}</p>
                @enderror
                @if(session('error'))
                    <p class="text-red-400 text-xs mt-2 text-center font-semibold">{{ session('error') }}</p>
                @endif
            </div>

            <button type="submit" class="w-full flex justify-center items-center gap-2 bg-amber-600 hover:bg-amber-500 text-white font-bold px-4 py-3 rounded-xl transition-all shadow-lg shadow-amber-900/20">
                Unlock Event
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
            <a href="{{ route('judge.dashboard') }}?start=1" class="block text-center mt-4 text-xs text-slate-500 hover:text-slate-300 transition-colors">Abort and return to dashboard</a>
        </form>
    </div>
</div>

@endsection
