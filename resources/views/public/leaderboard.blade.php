<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="10">
    <title>{{ $event->name }} — Live Scoreboard | FairScore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { background: radial-gradient(ellipse at top, #0f2040 0%, #050c1a 60%); }

        .rank-badge-1 { background:linear-gradient(135deg,#fbbf24,#f59e0b); color:#050c1a; font-weight:900; }
        .rank-badge-2 { background:linear-gradient(135deg,#94a3b8,#64748b); color:#fff; font-weight:900; }
        .rank-badge-3 { background:linear-gradient(135deg,#cd7c27,#92400e); color:#fff; font-weight:900; }
        .rank-badge-other { background:rgba(255,255,255,0.08); color:#64748b; font-weight:700; }

        .entry-1 { border-color:rgba(251,191,36,0.4)!important; background:linear-gradient(135deg,rgba(251,191,36,0.1),rgba(245,158,11,0.04))!important; }
        .entry-2 { border-color:rgba(148,163,184,0.3)!important; background:linear-gradient(135deg,rgba(148,163,184,0.08),transparent)!important; }
        .entry-3 { border-color:rgba(180,83,9,0.3)!important; background:linear-gradient(135deg,rgba(180,83,9,0.08),transparent)!important; }

        .score-bar-fill { height:6px; border-radius:3px; background:linear-gradient(90deg,#f59e0b,#2dd4bf); transition:width 1s ease; }
        .j-pill { display:inline-block; padding:2px 10px; border-radius:9999px; font-size:11px; font-family:monospace; font-weight:700; background:rgba(45,212,191,0.1); color:#2dd4bf; }
        .j-pill.na { background:rgba(255,255,255,0.04); color:#334155; }

        @keyframes fade-slide-in { from { opacity:0; transform:translateX(-20px); } to { opacity:1; transform:translateX(0); } }
        .entry-anim { animation:fade-slide-in 0.5s ease both; }

        @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.5;transform:scale(1.5);} }
        .live-dot { display:inline-block; width:8px; height:8px; border-radius:50%; background:#4ade80; animation:pulse-dot 1.4s ease infinite; vertical-align:middle; margin-right:4px; }
    </style>
</head>
<body class="min-h-screen">

{{-- ── Header ────────────────────────────────────────────────────────── --}}
<header class="px-6 py-5 flex items-center justify-between border-b" style="border-color:rgba(255,255,255,0.06);">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-base" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#050c1a;">
            FS
        </div>
        <div>
            <div class="text-xs font-semibold" style="color:#475569;text-transform:uppercase;letter-spacing:.08em;">
                @if($displayMode === 'criteria' && $displayCriteria)
                    Best in {{ $displayCriteria->name }}
                @else
                    Live Scoreboard
                @endif
            </div>
            <h1 class="text-xl font-black leading-tight" style="color:#f1f5f9;">{{ $event->name }}</h1>
        </div>
    </div>
    <div class="flex items-center gap-5">
        @if($displayMode === 'criteria' && $displayCriteria)
            <div class="hidden sm:block text-right">
                <div class="text-xs" style="color:#475569;">Showing</div>
                <div class="text-sm font-bold" style="color:#f59e0b;">Best in {{ $displayCriteria->name }}</div>
            </div>
        @endif
        @if($event->event_date)
            <div class="text-right hidden sm:block">
                <div class="text-xs" style="color:#475569;">Event Date</div>
                <div class="text-sm font-semibold" style="color:#94a3b8;">{{ $event->event_date->format('M d, Y') }}</div>
            </div>
        @endif
        <div class="text-right">
            <div class="text-xs" style="color:#475569;">Judges</div>
            <div class="text-sm font-semibold" style="color:#94a3b8;">{{ $judgeCount }}</div>
        </div>
        <div>
            @if($event->status === 'live')
                <span class="badge-live"><span class="live-dot"></span>LIVE</span>
            @elseif($event->status === 'completed')
                <span class="badge-completed">Final</span>
            @else
                <span class="badge-upcoming">Upcoming</span>
            @endif
        </div>
    </div>
</header>

{{-- ── Main ──────────────────────────────────────────────────────────── --}}
<main class="max-w-3xl mx-auto px-4 py-8">

    @if($leaderboard->isEmpty())
        <div class="text-center py-24">
            <div class="text-7xl mb-6">🏆</div>
            <h2 class="text-2xl font-black" style="color:#334155;">Scoreboard Empty</h2>
            <p class="mt-3 text-lg" style="color:#1e3a5f;">
                @if($event->status === 'upcoming') Event hasn't started yet.
                @else No scores have been submitted yet.
                @endif
            </p>
        </div>

    @elseif($displayMode === 'criteria' && $displayCriteria)
        {{-- ── CRITERIA MODE ─────────────────────────────────────────── --}}
        @php $winner = $leaderboard->first(); @endphp

        {{-- Winner banner --}}
        @if($winner && $winner['total'] > 0)
        <div class="mb-8 p-6 rounded-2xl text-center entry-anim" style="background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(245,158,11,0.04));border:2px solid rgba(245,158,11,0.35);">
            <div class="text-5xl mb-2">🥇</div>
            <div class="text-xs font-bold uppercase tracking-widest mb-1" style="color:#f59e0b;">Best in {{ $displayCriteria->name }}</div>
            <div class="text-3xl font-black" style="color:#f1f5f9;">{{ $winner['participant']->name }}</div>
            <div class="text-2xl font-black mt-1" style="color:#2dd4bf;">{{ number_format($winner['total'], 2) }} pts</div>
        </div>
        @endif

        {{-- Criterion ranking table --}}
        <h2 class="text-sm font-bold mb-3 uppercase tracking-widest" style="color:#334155;">
            Rankings — {{ $displayCriteria->name }}
        </h2>
        <div class="space-y-2">
            @foreach($leaderboard as $i => $row)
                @php
                    $rank   = $row['rank'];
                    $isTied = $leaderboard->where('rank', $rank)->count() > 1;
                    $entryClass = $rank===1?'entry-1':($rank===2?'entry-2':($rank===3?'entry-3':''));
                    $badgeClass = $rank===1?'rank-badge-1':($rank===2?'rank-badge-2':($rank===3?'rank-badge-3':'rank-badge-other'));
                    $maxTotal   = $leaderboard->max('total');
                    $barWidth   = $maxTotal > 0 ? ($row['total'] / $maxTotal * 100) : 0;
                @endphp
                <div class="entry-anim glass-card p-4 {{ $entryClass }}" style="animation-delay:{{ $i*0.06 }}s;">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-base flex-shrink-0 {{ $badgeClass }}">
                            {{ $rank }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($row['participant']->contestant_number)
                                        <span class="text-xs" style="color:#475569;">#{{ $row['participant']->contestant_number }}</span>
                                    @endif
                                    <span class="font-bold" style="color:#f1f5f9;">{{ $row['participant']->name }}</span>
                                    @if($isTied && $row['total'] > 0)
                                        <span style="font-size:9px;padding:1px 7px;border-radius:9999px;background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.25);font-weight:800;text-transform:uppercase;letter-spacing:.06em;">TIE</span>
                                    @endif
                                </div>
                                <span class="text-xl font-black ml-4 flex-shrink-0" style="color:{{ $rank===1?'#fbbf24':($rank<=3?'#e2e8f0':'#94a3b8') }};">
                                    {{ $row['total'] > 0 ? number_format($row['total'],2) : '—' }}
                                </span>
                            </div>
                            {{-- Judge score pills --}}
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                @foreach($row['judgeScores'] as $jId => $val)
                                    <span class="j-pill {{ $val > 0 ? '' : 'na' }}">{{ $val > 0 ? number_format($val,2) : '—' }}</span>
                                @endforeach
                            </div>
                            <div class="w-full rounded-full mt-2" style="background:rgba(255,255,255,0.06);">
                                <div class="score-bar-fill" style="width:{{ $barWidth }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        {{-- ── OVERALL MODE (default) ─────────────────────────────────── --}}

        {{-- Top 3 podium --}}
        @if($leaderboard->count() >= 3)
            <div class="flex items-end justify-center gap-3 mb-10">
                @php $second=$leaderboard[1]??null; @endphp
                @if($second)
                <div class="flex-1 max-w-[180px] text-center animate-fade-in-up delay-2">
                    <div class="text-4xl mb-2">🥈</div>
                    <div class="p-4 rounded-2xl" style="background:rgba(148,163,184,0.08);border:1px solid rgba(148,163,184,0.2);">
                        <div class="w-12 h-12 rounded-full mx-auto flex items-center justify-center font-black text-lg mb-2" style="background:rgba(148,163,184,0.2);color:#94a3b8;">#{{ $second->contestant_number??2 }}</div>
                        <p class="font-bold text-sm" style="color:#e2e8f0;">{{ $second->name }}</p>
                        <p class="text-2xl font-black mt-1" style="color:#94a3b8;">{{ number_format($second->weighted_avg,2) }}</p>
                    </div>
                </div>
                @endif

                @php $first=$leaderboard[0]??null; @endphp
                @if($first)
                <div class="flex-1 max-w-[200px] text-center animate-fade-in-up delay-1">
                    <div class="text-5xl mb-2">🏆</div>
                    <div class="p-5 rounded-2xl" style="background:rgba(251,191,36,0.1);border:2px solid rgba(251,191,36,0.35);">
                        <div class="w-14 h-14 rounded-full mx-auto flex items-center justify-center font-black text-xl mb-2" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#050c1a;">#{{ $first->contestant_number??1 }}</div>
                        <p class="font-black text-base" style="color:#f1f5f9;">{{ $first->name }}</p>
                        <p class="text-3xl font-black mt-1" style="color:#fbbf24;">{{ number_format($first->weighted_avg,2) }}</p>
                    </div>
                </div>
                @endif

                @php $third=$leaderboard[2]??null; @endphp
                @if($third)
                <div class="flex-1 max-w-[180px] text-center animate-fade-in-up delay-3">
                    <div class="text-4xl mb-2">🥉</div>
                    <div class="p-4 rounded-2xl" style="background:rgba(180,83,9,0.08);border:1px solid rgba(180,83,9,0.2);">
                        <div class="w-12 h-12 rounded-full mx-auto flex items-center justify-center font-black text-lg mb-2" style="background:rgba(180,83,9,0.2);color:#cd7c27;">#{{ $third->contestant_number??3 }}</div>
                        <p class="font-bold text-sm" style="color:#e2e8f0;">{{ $third->name }}</p>
                        <p class="text-2xl font-black mt-1" style="color:#cd7c27;">{{ number_format($third->weighted_avg,2) }}</p>
                    </div>
                </div>
                @endif
            </div>
        @endif

        <h2 class="text-sm font-bold mb-3 uppercase tracking-widest" style="color:#334155;">Full Rankings</h2>
        <div class="space-y-2">
            @foreach($leaderboard as $i => $entry)
                @php
                    $isTied    = $leaderboard->where('rank',$entry->rank)->count() > 1;
                    $entryClass= $entry->rank===1?'entry-1':($entry->rank===2?'entry-2':($entry->rank===3?'entry-3':''));
                    $badgeClass= $entry->rank===1?'rank-badge-1':($entry->rank===2?'rank-badge-2':($entry->rank===3?'rank-badge-3':'rank-badge-other'));
                    $maxScore  = $leaderboard->max('weighted_avg');
                    $barWidth  = $maxScore>0?($entry->weighted_avg/$maxScore*100):0;
                @endphp
                <div class="entry-anim glass-card p-4 {{ $entryClass }}" style="animation-delay:{{ $i*0.06 }}s;">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-base flex-shrink-0 {{ $badgeClass }}">{{ $entry->rank }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($entry->contestant_number)
                                        <span class="text-xs" style="color:#475569;">#{{ $entry->contestant_number }}</span>
                                    @endif
                                    <span class="font-bold" style="color:#f1f5f9;">{{ $entry->name }}</span>
                                    @if($isTied)
                                        <span style="font-size:9px;padding:1px 7px;border-radius:9999px;background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.25);font-weight:800;text-transform:uppercase;letter-spacing:.06em;">TIE</span>
                                    @endif
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <span class="text-xl font-black" style="color:{{ $entry->rank===1?'#fbbf24':($entry->rank<=3?'#e2e8f0':'#94a3b8') }};">
                                        {{ number_format($entry->weighted_avg,2) }}
                                    </span>
                                    <span class="text-xs ml-1" style="color:#475569;">pts</span>
                                </div>
                            </div>
                            <div class="w-full rounded-full" style="background:rgba(255,255,255,0.06);">
                                <div class="score-bar-fill" style="width:{{ $barWidth }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>

<footer class="text-center py-6 mt-8 border-t" style="border-color:rgba(255,255,255,0.04);">
    <p class="text-xs" style="color:#1e3a5f;">
        FairScore · Auto-refreshes every 10 seconds ·
        {{ $event->status==='live'?'Scoring in Progress':ucfirst($event->status) }}
        @if($displayMode==='criteria' && $displayCriteria)
            · Showing: Best in {{ $displayCriteria->name }}
        @endif
    </p>
</footer>

</body>
</html>
