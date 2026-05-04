@extends('layouts.admin')

@section('title', 'Create Event')
@section('page-title', 'Create Event')
@section('page-subtitle', 'Set up a new scoring event')

@section('header-actions')
    <a href="{{ route('admin.events.index') }}" class="btn-secondary">← Back to Events</a>
@endsection

@section('content')

<div class="max-w-xl animate-fade-in-up">
    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.events.store') }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="form-label">Event Name <span style="color:#f87171;">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="e.g. Grand Championship 2026" required>
                    @error('name')<p class="text-xs mt-1" style="color:#f87171;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-input" placeholder="Brief description of the event...">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="form-label">Event Date</label>
                    <input type="datetime-local" name="event_date" value="{{ old('event_date') }}" class="form-input">
                    @error('event_date')<p class="text-xs mt-1" style="color:#f87171;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Initial Status</label>
                    <select name="status" class="form-input" style="background-color: #000000; color: #ffffff;">
                        <option value="upcoming" {{ old('status','upcoming')==='upcoming' ? 'selected':'' }}>Upcoming</option>
                        <option value="live"     {{ old('status')==='live' ? 'selected':'' }}>Live</option>
                        <option value="completed"{{ old('status')==='completed' ? 'selected':'' }}>Completed</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Event PIN Code <span style="color:#f87171;">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="event_pin" name="pin" value="{{ old('pin') }}" class="form-input font-mono tracking-widest text-lg text-center w-full" placeholder="XXXXXX" maxlength="6" required>
                        <button type="button" onclick="document.getElementById('event_pin').value = Math.floor(100000 + Math.random() * 900000);" class="btn-secondary whitespace-nowrap">
                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            Generate
                        </button>
                    </div>
                    <p class="text-xs mt-1.5" style="color:#475569;">Judges will need this 6-digit PIN to access the scoring panel.</p>
                    @error('pin')<p class="text-xs mt-1" style="color:#f87171;">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-6 border-t" style="border-color:rgba(255,255,255,0.06);">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Create Event
                </button>
                <a href="{{ route('admin.events.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
