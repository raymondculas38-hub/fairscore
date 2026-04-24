@extends('layouts.admin')

@section('title', 'Scoreboard — ' . $event->name)
@section('page-title', 'Scoreboard')
@section('page-subtitle', $event->name)

@section('header-actions')
    <a href="{{ route('admin.scoreboard.index') }}" class="btn-secondary">← All Events</a>
    <a href="{{ route('leaderboard.show', $event) }}" target="_blank" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        Public Scoreboard
    </a>
@endsection

@section('content')
<style>
    /* ── Tab system ─────────────────────────────────────────────────── */
    .sb-tab { display: none; }
    .sb-tab.active { display: block; }
    .sb-tab-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600;
        cursor: pointer; transition: all .18s ease;
        background: rgba(255,255,255,0.04);
        color: #64748b;
        border: 1px solid rgba(255,255,255,0.07);
        white-space: nowrap;
    }
    .sb-tab-btn:hover { background: rgba(255,255,255,0.08); color: #94a3b8; }
    .sb-tab-btn.active {
        background: rgba(245,158,11,0.15) !important;
        color: #f59e0b !important;
        border-color: rgba(245,158,11,0.35) !important;
    }

    /* ── Winner row ─────────────────────────────────────────────────── */
    .winner-row { background: linear-gradient(90deg, rgba(245,158,11,0.08), transparent) !important; }
    .winner-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 10px; border-radius: 9999px; font-size: 10px;
        font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
        background: rgba(245,158,11,0.2); color: #f59e0b;
        border: 1px solid rgba(245,158,11,0.35);
    }
    .tie-badge {
        display: inline-flex; align-items: center;
        padding: 1px 7px; border-radius: 9999px; font-size: 9px;
        font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
        background: rgba(239,68,68,0.15); color: #f87171;
        border: 1px solid rgba(239,68,68,0.25);
    }
    /* ── Set Public button ──────────────────────────────────────────── */
    .btn-set-public {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 9px; font-size: 12px; font-weight: 700;
        cursor: pointer; border: none; transition: all .18s ease;
        background: rgba(99,102,241,0.15); color: #818cf8;
        border: 1px solid rgba(99,102,241,0.3);
    }
    .btn-set-public:hover { background: rgba(99,102,241,0.3); }
    .btn-set-public.active-public {
        background: rgba(34,197,94,0.15) !important; color: #4ade80 !important;
        border-color: rgba(34,197,94,0.3) !important;
        cursor: default;
    }

    /* ── Judge score pill ───────────────────────────────────────────── */
    .j-score {
        display: inline-block; min-width: 52px; padding: 3px 8px;
        border-radius: 8px; text-align: center; font-family: monospace;
        font-size: 12px; font-weight: 700;
        background: rgba(255,255,255,0.05);
        color: #94a3b8;
    }
    .j-score.scored { background: rgba(45,212,191,0.1); color: #2dd4bf; }
    .j-score.pending { background: rgba(255,255,255,0.04); color: #334155; }

    /* ── Pulse dot for live ─────────────────────────────────────────── */
    @keyframes pulse-dot { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.5; transform:scale(1.5); } }
    .live-dot { width:8px; height:8px; border-radius:50%; background:#4ade80; animation: pulse-dot 1.4s ease infinite; }

    /* ── Auto-refresh countdown ─────────────────────────────────────── */
    #refresh-bar {
        height: 3px; background: rgba(245,158,11,0.15);
        border-radius: 2px; overflow: hidden; margin-bottom: 16px;
    }
    #refresh-fill {
        height: 100%; background: linear-gradient(90deg,#f59e0b,#2dd4bf);
        transition: width 1s linear; border-radius: 2px;
    }
</style>

<div class="animate-fade-in-up space-y-5">

    {{-- ── Status Bar ────────────────────────────────────────────────── --}}
    <div class="glass-card px-5 py-3 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            @if($event->status === 'live')
                <div class="live-dot"></div>
                <span class="text-sm font-semibold" style="color:#4ade80;">Live Scoring in Progress</span>
            @elseif($event->status === 'completed')
                <span class="text-sm font-semibold" style="color:#94a3b8;">🏁 Final Results</span>
            @else
                <span class="text-sm font-semibold" style="color:#f59e0b;">⏳ Event Upcoming</span>
            @endif
        </div>
        <div class="flex items-center gap-4 ml-auto text-xs" style="color:#475569;">
            <span>{{ $event->judges->count() }} Judge{{ $event->judges->count() !== 1 ? 's' : '' }}</span>
            <span>·</span>
            <span>{{ $event->participants->count() }} Contestant{{ $event->participants->count() !== 1 ? 's' : '' }}</span>
            <span>·</span>
            <span>{{ $event->criteria->count() }} Criteria</span>
            @if($event->status === 'live')
                <span>·</span>
                <span id="refresh-counter" style="color:#f59e0b;">Refreshing in <strong id="countdown">10</strong>s</span>
            @endif
        </div>
    </div>

    {{-- Auto-refresh progress bar (live only) --}}
    @if($event->status === 'live')
        <div id="refresh-bar"><div id="refresh-fill" style="width:100%;"></div></div>
    @endif

    {{-- ── Category Winners Row ───────────────────────────────────────── --}}
    @if(count($categoryWinners) > 0)
    <div>
        <h2 class="text-xs font-bold uppercase tracking-widest mb-3" style="color:#334155;">🏆 Category Winners</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($categoryWinners as $w)
                <div class="glass-card p-4 text-center" style="border-color:rgba(245,158,11,0.2);background:linear-gradient(135deg,rgba(245,158,11,0.06),transparent);">
                    <div class="text-2xl mb-1">🥇</div>
                    <div class="text-[10px] uppercase font-bold tracking-widest mb-1" style="color:#f59e0b;">{{ $w['criterion']->name }}</div>
                    <div class="text-sm font-black" style="color:#f1f5f9;">{{ $w['winner']->name }}</div>
                    <div class="text-xs mt-1 font-mono font-bold" style="color:#2dd4bf;">{{ number_format($w['total'], 2) }} pts</div>
                </div>
            @endforeach
            {{-- Overall winner --}}
            @if($overallLeaderboard->count() > 0)
                @php $ow = $overallLeaderboard->first(); @endphp
                <div class="glass-card p-4 text-center" style="border-color:rgba(99,102,241,0.3);background:linear-gradient(135deg,rgba(99,102,241,0.1),transparent);">
                    <div class="text-2xl mb-1">🏆</div>
                    <div class="text-[10px] uppercase font-bold tracking-widest mb-1" style="color:#818cf8;">Overall</div>
                    <div class="text-sm font-black" style="color:#f1f5f9;">{{ $ow->name }}</div>
                    <div class="text-xs mt-1 font-mono font-bold" style="color:#818cf8;">{{ number_format($ow->weighted_avg, 2) }} avg</div>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Tab Bar ────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-2" id="sb-tabs">
        <button class="sb-tab-btn active" onclick="sbSwitch(event,'overall')">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.381-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            Overall Rankings
        </button>
        @foreach($breakdown as $critId => $data)
            <button class="sb-tab-btn" data-crit="{{ $critId }}" onclick="sbSwitch(event,'crit-{{ $critId }}')">
                {{ $data['criterion']->name }}
                <span style="font-size:10px;padding:1px 6px;border-radius:9999px;background:rgba(255,255,255,0.08);color:#64748b;">
                    {{ number_format($data['criterion']->weight, 2) }}×
                </span>
            </button>
        @endforeach
    </div>

    {{-- ── Overall Rankings Tab ──────────────────────────────────────── --}}
    <div id="sb-overall" class="sb-tab active glass-card overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between gap-4" style="border-color:rgba(255,255,255,0.06);">
            <div>
                <h3 class="font-bold text-sm" style="color:#e2e8f0;">Overall Rankings — All Criteria Combined</h3>
                <p class="text-xs mt-0.5" style="color:#475569;">Weighted average across all judges &amp; criteria</p>
            </div>
            <form method="POST" action="{{ route('admin.scoreboard.setDisplay', $event) }}">
                @csrf
                <input type="hidden" name="mode" value="overall">
                <button type="submit" class="btn-set-public {{ $event->public_display_mode === 'overall' ? 'active-public' : '' }}">
                    @if($event->public_display_mode === 'overall')
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Currently Public
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Set as Public
                    @endif
                </button>
            </form>
        </div>

        @if($overallLeaderboard->isEmpty())
            <div class="px-5 py-10 text-center" style="color:#334155;">
                <div class="text-4xl mb-3">📊</div>
                <p class="text-sm">No scores have been submitted yet.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" style="color:#cbd5e1;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.04);border-bottom:1px solid rgba(255,255,255,0.06);">
                        <th class="px-5 py-3 font-semibold w-24">Rank</th>
                        <th class="px-5 py-3 font-semibold">Contestant</th>
                        @foreach($breakdown as $critId => $catData)
                            <th class="px-4 py-3 font-semibold text-center text-xs" style="color:#64748b;">{{ $catData['criterion']->name }}</th>
                        @endforeach
                        <th class="px-5 py-3 font-semibold text-right" style="color:#818cf8;">Weighted Avg</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                    @foreach($overallLeaderboard as $entry)
                        @php
                            $isWinner = $entry->rank === 1;
                            // Get total per criterion for this participant
                            $critTotals = [];
                            foreach($breakdown as $critId => $catData) {
                                foreach($catData['participants'] as $p) {
                                    if($p['participant']->id === $entry->id) {
                                        $critTotals[$critId] = $p['total'];
                                        break;
                                    }
                                }
                            }
                        @endphp
                        @php $isTied = $overallLeaderboard->where('rank', $entry->rank)->count() > 1; @endphp
                        <tr class="{{ $isWinner ? 'winner-row' : 'hover:bg-white/5' }} transition-colors">
                            <td class="px-5 py-3">
                                @if($entry->rank === 1)
                                    <span class="winner-badge">🏆 {{ $isTied ? 'TIED 1st' : '1st' }}</span>
                                @elseif($entry->rank === 2)
                                    <span class="text-slate-400 font-bold text-xs">🥈 {{ $isTied ? 'TIED ' : '' }}2nd</span>
                                @elseif($entry->rank === 3)
                                    <span style="color:#cd7c27;" class="font-bold text-xs">🥉 {{ $isTied ? 'TIED ' : '' }}3rd</span>
                                @else
                                    <span class="text-slate-600 text-xs font-bold">{{ $entry->rank }}th{{ $isTied ? ' (Tie)' : '' }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-semibold" style="color:{{ $isWinner ? '#f1f5f9' : '#94a3b8' }};">{{ $entry->name }}</div>
                                @if($entry->contestant_number)
                                    <div class="text-[10px]" style="color:#334155;">#{{ $entry->contestant_number }}</div>
                                @endif
                            </td>
                            @foreach($breakdown as $critId => $catData)
                                <td class="px-4 py-3 text-center">
                                    <span class="j-score {{ ($critTotals[$critId] ?? 0) > 0 ? 'scored' : 'pending' }}">
                                        {{ isset($critTotals[$critId]) && $critTotals[$critId] > 0 ? number_format($critTotals[$critId], 1) : '—' }}
                                    </span>
                                </td>
                            @endforeach
                            <td class="px-5 py-3 text-right">
                                <span class="font-mono font-black text-base" style="color:{{ $isWinner ? '#818cf8' : '#475569' }};">
                                    {{ number_format($entry->weighted_avg, 2) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Per-Criterion Tabs ─────────────────────────────────────────── --}}
    @foreach($breakdown as $critId => $data)
        <div id="sb-crit-{{ $critId }}" class="sb-tab glass-card overflow-hidden">
            <div class="px-5 py-4 border-b" style="border-color:rgba(255,255,255,0.06);">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-base" style="color:#e2e8f0;">
                            Best in {{ $data['criterion']->name }}
                        </h3>
                        <p class="text-xs mt-0.5" style="color:#475569;">
                            Max score: <strong>{{ $data['criterion']->max_score }}</strong> &nbsp;·&nbsp;
                            Weight: <strong>{{ $data['criterion']->weight }}×</strong> &nbsp;·&nbsp;
                            {{ $event->judges->count() }} judge{{ $event->judges->count() !== 1 ? 's' : '' }} scoring
                        </p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                    {{-- Set as Public button --}}
                    <form method="POST" action="{{ route('admin.scoreboard.setDisplay', $event) }}">
                        @csrf
                        <input type="hidden" name="mode" value="criteria">
                        <input type="hidden" name="criteria_id" value="{{ $data['criterion']->id }}">
                        @php $isActivePublic = $event->public_display_mode === 'criteria' && $event->public_criteria_id == $data['criterion']->id; @endphp
                        <button type="submit" class="btn-set-public {{ $isActivePublic ? 'active-public' : '' }}">
                            @if($isActivePublic)
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Currently Public
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Set as Public
                            @endif
                        </button>
                    </form>
                    {{-- Winner callout --}}
                    @php $topP = $data['participants'][0] ?? null; @endphp
                    @if($topP && $topP['total'] > 0)
                        <div class="flex items-center gap-3 px-4 py-2 rounded-xl" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);">
                            <span class="text-xl">🥇</span>
                            <div>
                                <div class="text-[10px] uppercase font-bold tracking-widest" style="color:#f59e0b;">Best in {{ $data['criterion']->name }}</div>
                                <div class="text-sm font-black" style="color:#f1f5f9;">{{ $topP['participant']->name }}</div>
                            </div>
                            <div class="text-xl font-black ml-2" style="color:#2dd4bf;">{{ number_format($topP['total'], 2) }}</div>
                        </div>
                    @endif
                    </div>{{-- end flex wrapper --}}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" style="color:#cbd5e1;">
                    <thead>
                        <tr style="background:rgba(255,255,255,0.04);border-bottom:1px solid rgba(255,255,255,0.06);">
                            <th class="px-5 py-3 font-semibold w-24">Rank</th>
                            <th class="px-5 py-3 font-semibold">Contestant</th>
                            @foreach($event->judges as $judge)
                                <th class="px-4 py-3 text-center font-semibold text-xs" style="color:#64748b;">
                                    <div>{{ $judge->name }}</div>
                                    <div style="color:#334155;font-weight:400;">Judge</div>
                                </th>
                            @endforeach
                            <th class="px-5 py-3 font-semibold text-right" style="color:#2dd4bf;">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                        @foreach($data['participants'] as $p)
                            @php
                                $isWinner = $p['rank'] === 1 && $p['total'] > 0;
                                $critTied = collect($data['participants'])->where('rank', $p['rank'])->where('total', '>', 0)->count() > 1;
                            @endphp
                            <tr class="{{ $isWinner ? 'winner-row' : 'hover:bg-white/5' }} transition-colors">
                                <td class="px-5 py-3">
                                    @if($isWinner)
                                        <span class="winner-badge">🥇 {{ $critTied ? 'TIED' : 'Best' }}</span>
                                    @elseif($p['rank'] === 2 && $p['total'] > 0)
                                        <span class="text-slate-400 font-bold text-xs">🥈 {{ $critTied ? 'TIED ' : '' }}2nd</span>
                                    @elseif($p['rank'] === 3 && $p['total'] > 0)
                                        <span style="color:#cd7c27;" class="font-bold text-xs">🥉 {{ $critTied ? 'TIED ' : '' }}3rd</span>
                                    @else
                                        <span class="text-slate-600 text-xs">{{ $p['total'] > 0 ? $p['rank'].'th'.($critTied?' (Tie)':'') : '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-semibold" style="color:{{ $isWinner ? '#f1f5f9' : '#94a3b8' }};">
                                        {{ $p['participant']->name }}
                                    </div>
                                    @if($p['participant']->contestant_number)
                                        <div class="text-[10px]" style="color:#334155;">#{{ $p['participant']->contestant_number }}</div>
                                    @endif
                                </td>
                                @foreach($event->judges as $judge)
                                    @php $val = $p['judgeScores'][$judge->id] ?? null; @endphp
                                    <td class="px-4 py-3 text-center">
                                        <span class="j-score {{ $val !== null && $val > 0 ? 'scored' : 'pending' }}">
                                            {{ $val !== null && $val > 0 ? number_format($val, 2) : '—' }}
                                        </span>
                                    </td>
                                @endforeach
                                <td class="px-5 py-3 text-right">
                                    <span class="font-mono font-black text-base" style="color:{{ $isWinner ? '#2dd4bf' : '#475569' }};">
                                        {{ $p['total'] > 0 ? number_format($p['total'], 2) : '—' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

</div>

<script>
    // ── Tab switching ──────────────────────────────────────────────────────
    const STORAGE_KEY = 'sb_active_tab_{{ $event->id }}';

    function sbSwitch(e, tabId) {
        document.querySelectorAll('.sb-tab').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.sb-tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('sb-' + tabId)?.classList.add('active');
        if (e?.currentTarget) e.currentTarget.classList.add('active');
        localStorage.setItem(STORAGE_KEY, tabId);
    }

    // Restore last active tab
    document.addEventListener('DOMContentLoaded', () => {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved && document.getElementById('sb-' + saved)) {
            const btn = [...document.querySelectorAll('.sb-tab-btn')]
                .find(b => b.getAttribute('onclick')?.includes("'" + saved + "'"));
            if (btn) sbSwitch({ currentTarget: btn }, saved);
        }
    });

    // ── Live auto-refresh (only for live events) ───────────────────────────
    @if($event->status === 'live')
    let seconds = 10;
    const fill = document.getElementById('refresh-fill');
    const cntd = document.getElementById('countdown');
    const timer = setInterval(() => {
        seconds--;
        if (cntd) cntd.textContent = seconds;
        if (fill) fill.style.width = (seconds / 10 * 100) + '%';
        if (seconds <= 0) {
            clearInterval(timer);
            window.location.reload();
        }
    }, 1000);
    @endif
</script>
@endsection
