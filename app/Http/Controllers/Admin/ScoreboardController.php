<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreboardController extends Controller
{
    /**
     * List all events belonging to this admin (for selection).
     */
    public function index()
    {
        $events = Event::withCount(['participants', 'judges', 'criteria', 'scores'])
            ->latest()
            ->get();

        return view('admin.scoreboard.index', compact('events'));
    }

    /**
     * Show the full score breakdown for a specific event.
     */
    public function show(Event $event)
    {
        $event->load(['criteria', 'judges', 'participants']);

        $allScores = Score::where('event_id', $event->id)->get();

        // ── Build per-criterion breakdown ─────────────────────────────────
        $breakdown = [];

        foreach ($event->criteria as $criterion) {
            $catData = [
                'criterion'    => $criterion,
                'participants' => [],
            ];

            foreach ($event->participants as $participant) {
                $pScores     = $allScores->where('participant_id', $participant->id)
                                         ->where('criteria_id', $criterion->id);
                $judgeScores = [];
                $total       = 0;

                foreach ($event->judges as $judge) {
                    $s   = $pScores->where('judge_id', $judge->id)->first();
                    $val = $s ? (float) $s->score : null; // null = not yet scored
                    $judgeScores[$judge->id] = $val;
                    $total += ($val ?? 0);
                }

                $catData['participants'][] = [
                    'participant' => $participant,
                    'judgeScores' => $judgeScores,
                    'total'       => $total,
                    'rank'        => 0,
                ];
            }

            // Sort by total descending
            usort($catData['participants'], fn($a, $b) => $b['total'] <=> $a['total']);

            // Assign ranks (handle ties)
            $rank = 1;
            foreach ($catData['participants'] as $i => &$p) {
                if ($i > 0 && $p['total'] < $catData['participants'][$i - 1]['total']) {
                    $rank = $i + 1;
                }
                $p['rank'] = $rank;
            }
            unset($p);

            $breakdown[$criterion->id] = $catData;
        }

        // ── Overall weighted leaderboard ──────────────────────────────────
        $overallLeaderboard = DB::table('scores')
            ->join('participants', 'scores.participant_id', '=', 'participants.id')
            ->join('criteria',     'scores.criteria_id',   '=', 'criteria.id')
            ->where('scores.event_id', $event->id)
            ->select(
                'participants.id',
                'participants.name',
                'participants.contestant_number',
                DB::raw('SUM(scores.score * criteria.weight) / SUM(criteria.weight) AS weighted_avg'),
                DB::raw('SUM(scores.score) AS total_raw_score')
            )
            ->groupBy('participants.id', 'participants.name', 'participants.contestant_number')
            ->orderByDesc('weighted_avg')
            ->get();

        // Assign overall ranks
        $rank = 1;
        foreach ($overallLeaderboard as $i => $entry) {
            if ($i > 0 && $entry->weighted_avg < $overallLeaderboard[$i - 1]->weighted_avg) {
                $rank = $i + 1;
            }
            $entry->rank = $rank;
        }

        // ── Category winners (Best in X) ──────────────────────────────────
        $categoryWinners = [];
        foreach ($breakdown as $critId => $data) {
            $winner = $data['participants'][0] ?? null;
            if ($winner) {
                $categoryWinners[$critId] = [
                    'criterion'   => $data['criterion'],
                    'winner'      => $winner['participant'],
                    'total'       => $winner['total'],
                ];
            }
        }

        return view('admin.scoreboard.show', compact(
            'event',
            'breakdown',
            'overallLeaderboard',
            'categoryWinners'
        ));
    }

    /**
     * Set which view is shown on the public scoreboard.
     * POST /admin/scoreboard/{event}/set-display
     */
    public function setDisplay(Request $request, Event $event)
    {
        $validated = $request->validate([
            'mode'        => 'required|in:overall,criteria',
            'criteria_id' => 'nullable|exists:criteria,id',
        ]);

        $event->update([
            'public_display_mode'   => $validated['mode'],
            'public_criteria_id'    => $validated['mode'] === 'criteria'
                                        ? ($validated['criteria_id'] ?? null)
                                        : null,
        ]);

        $label = $validated['mode'] === 'overall'
            ? 'Overall Rankings'
            : optional(\App\Models\Criteria::find($validated['criteria_id']))->name ?? 'Selected Category';

        return back()->with('success', "Public scoreboard now shows: {$label}");
    }
}
