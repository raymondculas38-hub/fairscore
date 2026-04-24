@extends('layouts.admin')

@section('title', 'Edit Event — ' . $event->name)
@section('page-title', 'Edit Event')
@section('page-subtitle', $event->name)

@section('header-actions')
    <a href="{{ route('admin.events.index') }}" class="btn-secondary">← Back</a>
    <a href="{{ route('leaderboard.show', $event) }}" target="_blank" class="btn-secondary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
        Scoreboard
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 gap-5 lg:grid-cols-5 animate-fade-in-up">

    {{-- Main form --}}
    <div class="lg:col-span-3 space-y-5">

        {{-- Event details --}}
        <div class="glass-card p-6">
            <h3 class="text-sm font-bold mb-5" style="color:#e2e8f0;">Event Details</h3>
            <form method="POST" action="{{ route('admin.events.update', $event) }}">
                @csrf @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="form-label">Event Name</label>
                        <input type="text" name="name" value="{{ old('name', $event->name) }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="2" class="form-input">{{ old('description', $event->description) }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Event Date</label>
                            <input type="datetime-local" name="event_date"
                                value="{{ old('event_date', $event->event_date ? $event->event_date->format('Y-m-d\TH:i') : '') }}"
                                class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input" style="background-color: #000000; color: #ffffff;">
                                <option value="upcoming"  {{ $event->status==='upcoming'  ? 'selected':'' }}>Upcoming</option>
                                <option value="live"      {{ $event->status==='live'      ? 'selected':'' }}>Live</option>
                                <option value="completed" {{ $event->status==='completed' ? 'selected':'' }}>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Event PIN Code <span style="color:#f87171;">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="event_pin" name="pin" value="{{ old('pin', $event->pin) }}" class="form-input font-mono tracking-widest text-lg text-center w-full" placeholder="XXXXXX" maxlength="6" required>
                            <button type="button" onclick="document.getElementById('event_pin').value = Math.floor(100000 + Math.random() * 900000);" class="btn-secondary whitespace-nowrap">
                                <svg class="w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                Generate
                            </button>
                        </div>
                        <p class="text-xs mt-1.5" style="color:#475569;">Changing this will require judges to enter the new PIN.</p>
                        @error('pin')<p class="text-xs mt-1" style="color:#f87171;">{{ $message }}</p>@enderror
                    </div>

                    {{-- Judge Assignment --}}
                    <div>
                        <label class="form-label">Assign Judges</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            @foreach($allJudges as $judge)
                                <label class="flex items-center gap-2.5 px-3 py-2 rounded-lg cursor-pointer transition-colors"
                                    style="background:rgba(255,255,255,0.04);border:1px solid {{ in_array($judge->id, $assignedJudges) ? 'rgba(245,158,11,0.4)' : 'rgba(255,255,255,0.07)' }};">
                                    <input type="checkbox" name="judges[]" value="{{ $judge->id }}"
                                        {{ in_array($judge->id, $assignedJudges) ? 'checked' : '' }}
                                        class="rounded" style="accent-color:#f59e0b;">
                                    <span class="text-sm" style="color:#e2e8f0;">{{ $judge->name }}</span>
                                    <span class="text-xs ml-auto" style="color:#475569;">@{{ $judge->username }}</span>
                                </label>
                            @endforeach
                            @if($allJudges->isEmpty())
                                <p class="col-span-2 text-xs py-2" style="color:#475569;">No judges created yet. <a href="{{ route('admin.judges.index') }}" class="text-amber-400 underline">Add judges →</a></p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-5 pt-5 border-t" style="border-color:rgba(255,255,255,0.06);">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Right column quick links --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Quick action cards --}}
        <div class="glass-card p-5">
            <h3 class="text-sm font-bold mb-4" style="color:#e2e8f0;">Manage Event Data</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.participants.index', $event) }}"
                   class="flex items-center justify-between p-3 rounded-xl transition-colors"
                   style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);"
                   onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(45,212,191,0.15);">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#2dd4bf" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <span class="text-sm font-semibold" style="color:#e2e8f0;">Participants</span>
                    </div>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#475569" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>

                <a href="{{ route('admin.criteria.index', $event) }}"
                   class="flex items-center justify-between p-3 rounded-xl transition-colors"
                   style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);"
                   onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(245,158,11,0.15);">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.381-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                        </div>
                        <span class="text-sm font-semibold" style="color:#e2e8f0;">Scoring Criteria</span>
                    </div>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#475569" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>

                <a href="{{ route('leaderboard.show', $event) }}" target="_blank"
                   class="flex items-center justify-between p-3 rounded-xl transition-colors"
                   style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);"
                   onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(99,102,241,0.15);">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#818cf8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <span class="text-sm font-semibold" style="color:#e2e8f0;">Live Scoreboard ↗</span>
                    </div>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#475569" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>

                <a href="{{ route('admin.events.breakdown', $event) }}"
                   class="flex items-center justify-between p-3 rounded-xl transition-colors"
                   style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);"
                   onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.04)'">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(236,72,153,0.15);">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#f472b6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                        </div>
                        <span class="text-sm font-semibold" style="color:#e2e8f0;">Detailed Score Breakdown</span>
                    </div>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="#475569" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="glass-card p-5" style="border-color:rgba(239,68,68,0.2);">
            <h3 class="text-sm font-bold mb-3" style="color:#f87171;">Danger Zone</h3>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Permanently delete this event and ALL its data? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger w-full justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Delete Event
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
