<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Score;
use App\Models\Criteria;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function show(Event $event)
    {
        $judgeCount = $event->judges()->count();

        // ── Branch: what did the admin set as public? ─────────────────────
        $mode       = $event->public_display_mode ?? 'overall';
        $criteriaId = $event->public_criteria_id;

        if ($mode === 'criteria' && $criteriaId) {
            return $this->showCriteria($event, $criteriaId, $judgeCount);
        }

        return $this->showOverall($event, $judgeCount);
    }

    // ── Overall weighted average ──────────────────────────────────────────
    private function showOverall(Event $event, int $judgeCount)
    {
        $leaderboard = DB::table('scores')
            ->join('participants', 'scores.participant_id', '=', 'participants.id')
            ->join('criteria',     'scores.criteria_id',   '=', 'criteria.id')
            ->where('scores.event_id', $event->id)
            ->select(
                'participants.id',
                'participants.name',
                'participants.contestant_number',
                DB::raw('SUM(scores.score * criteria.weight) / SUM(criteria.weight) AS weighted_avg'),
                DB::raw('COUNT(DISTINCT scores.judge_id) AS judge_count'),
                DB::raw('SUM(scores.score) AS total_raw_score')
            )
            ->groupBy('participants.id', 'participants.name', 'participants.contestant_number')
            ->orderByDesc('weighted_avg')
            ->get();

        $rank = 1;
        foreach ($leaderboard as $i => $entry) {
            if ($i > 0 && $entry->weighted_avg < $leaderboard[$i - 1]->weighted_avg) {
                $rank = $i + 1;
            }
            $entry->rank = $rank;
        }

        $displayMode     = 'overall';
        $displayCriteria = null;

        return view('public.leaderboard', compact(
            'event', 'leaderboard', 'judgeCount', 'displayMode', 'displayCriteria'
        ));
    }

    // ── Single-criterion view ─────────────────────────────────────────────
    private function showCriteria(Event $event, int $criteriaId, int $judgeCount)
    {
        $displayCriteria = Criteria::find($criteriaId);
        $event->load(['judges', 'participants']);

        $scores = Score::where('event_id', $event->id)
                       ->where('criteria_id', $criteriaId)
                       ->get();

        $rows = [];
        foreach ($event->participants as $participant) {
            $pScores     = $scores->where('participant_id', $participant->id);
            $judgeScores = [];
            $total       = 0;
            foreach ($event->judges as $judge) {
                $s   = $pScores->where('judge_id', $judge->id)->first();
                $val = $s ? (float) $s->score : null;
                $judgeScores[$judge->id] = $val;
                $total += ($val ?? 0);
            }
            $rows[] = compact('participant', 'judgeScores', 'total');
        }

        // Sort & rank
        usort($rows, fn($a, $b) => $b['total'] <=> $a['total']);
        $rank = 1;
        foreach ($rows as $i => &$r) {
            if ($i > 0 && $r['total'] < $rows[$i - 1]['total']) {
                $rank = $i + 1;
            }
            $r['rank'] = $rank;
        }
        unset($r);

        $leaderboard = collect($rows);
        $displayMode = 'criteria';

        return view('public.leaderboard', compact(
            'event', 'leaderboard', 'judgeCount', 'displayMode', 'displayCriteria'
        ));
    }
}
