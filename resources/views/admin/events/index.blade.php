@extends('layouts.admin')

@section('title', 'Events')
@section('page-title', 'Events')
@section('page-subtitle', 'Manage scoring events')

@section('header-actions')
    <a href="{{ route('admin.events.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Event
    </a>
@endsection

@section('content')

<div class="glass-card animate-fade-in-up">
    <table class="data-table">
        <thead>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Status</th>
                <th>Participants</th>
                <th>Criteria</th>
                <th>Judges</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
                <tr class="animate-fade-in-up">
                    <td>
                        <div class="font-semibold" style="color:#e2e8f0;">{{ $event->name }}</div>
                        @if($event->pin)
                            <div class="mt-1 flex items-center gap-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.25);">
                                    PIN: {{ $event->pin }}
                                </span>
                            </div>
                        @endif
                        @if($event->description)
                            <div class="text-xs mt-1 truncate max-w-xs" style="color:#475569;">{{ $event->description }}</div>
                        @endif
                    </td>
                    <td style="color:#64748b;">
                        {{ $event->event_date ? $event->event_date->format('M d, Y') : '—' }}
                    </td>
                    <td>
                        @if($event->status === 'live')
                            <span class="badge-live">LIVE</span>
                        @elseif($event->status === 'completed')
                            <span class="badge-completed">Completed</span>
                        @else
                            <span class="badge-upcoming">Upcoming</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.participants.index', $event) }}" class="btn-teal">
                            {{ $event->participants_count }}
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('admin.criteria.index', $event) }}" class="btn-teal">
                            {{ $event->criteria_count }}
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </td>
                    <td style="color:#94a3b8;">{{ $event->judges_count }}</td>
                    <td>
                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Toggle Status --}}
                            <form method="POST" action="{{ route('admin.events.toggle', $event) }}">
                                @csrf
                                <button type="submit" class="btn-secondary text-xs py-1.5 px-2.5">
                                    @if($event->status === 'upcoming') ▶ Go Live
                                    @elseif($event->status === 'live') ✓ Complete
                                    @else ↺ Reset @endif
                                </button>
                            </form>

                            {{-- Edit --}}
                            <a href="{{ route('admin.events.edit', $event) }}" class="btn-teal">Edit</a>

                            {{-- Scoreboard --}}
                            <a href="{{ route('leaderboard.show', $event) }}" target="_blank" class="btn-secondary text-xs py-1.5 px-2.5" title="Open Scoreboard">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            </a>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete this event and all its data?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-14" style="color:#334155;">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <p class="text-sm mb-3">No events created yet.</p>
                        <a href="{{ route('admin.events.create') }}" class="btn-primary">Create First Event</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
