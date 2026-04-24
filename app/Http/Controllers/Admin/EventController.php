<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount(['participants', 'criteria', 'judges'])->latest()->get();
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'event_date'  => 'nullable|date',
            'status'      => 'required|in:upcoming,live,completed',
            'pin'         => 'required|string|size:6',
        ]);

        $validated['admin_id'] = auth()->id();
        Event::create($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully!');
    }

    public function edit(Event $event)
    {
        $allJudges      = User::where('role', 'JUDGE')->get();
        $assignedJudges = $event->judges->pluck('id')->toArray();
        return view('admin.events.edit', compact('event', 'allJudges', 'assignedJudges'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'event_date'  => 'nullable|date',
            'status'      => 'required|in:upcoming,live,completed',
            'pin'         => 'required|string|size:6',
        ]);

        $event->update($validated);

        // Sync judge assignments
        if ($request->has('judges')) {
            $changes = $event->judges()->sync($request->input('judges', []));

            // Notify newly assigned judges
            if (!empty($changes['attached'])) {
                $newJudges = User::whereIn('id', $changes['attached'])->get();
                foreach ($newJudges as $judge) {
                    $judge->notify(new \App\Notifications\EventAssignedNotification($event));
                }
            }
        } else {
            $event->judges()->detach();
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted.');
    }

    public function toggleStatus(Event $event)  
    {
        $next = match($event->status) {
            'upcoming'  => 'live',
            'live'      => 'completed',
            'completed' => 'upcoming',
        };
        $event->update(['status' => $next]);

        return back()->with('success', "Event status changed to \"{$next}\".");
    }

    public function breakdown(Event $event)
    {
        $event->load(['criteria', 'judges', 'participants']);
        
        $scores = \App\Models\Score::where('event_id', $event->id)->get();
        
        $breakdown = [];
        
        foreach ($event->criteria as $criterion) {
            $catData = [
                'criterion' => $criterion,
                'participants' => []
            ];
            
            foreach ($event->participants as $participant) {
                $pScores = $scores->where('participant_id', $participant->id)->where('criteria_id', $criterion->id);
                $judgeScores = [];
                $total = 0;
                
                foreach ($event->judges as $judge) {
                    $s = $pScores->where('judge_id', $judge->id)->first();
                    $val = $s ? $s->score : 0;
                    $judgeScores[$judge->id] = $val;
                    $total += $val;
                }
                
                $catData['participants'][] = [
                    'participant' => $participant,
                    'judgeScores' => $judgeScores,
                    'total' => $total,
                    'rank' => 0,
                ];
            }
            
            // Sort by total descending
            usort($catData['participants'], function($a, $b) {
                return $b['total'] <=> $a['total'];
            });
            
            // Assign ranks
            $rank = 1;
            foreach ($catData['participants'] as $i => &$p) {
                if ($i > 0 && $p['total'] < $catData['participants'][$i - 1]['total']) {
                    $rank = $i + 1;
                }
                $p['rank'] = $rank;
            }
            
            $breakdown[$criterion->id] = $catData;
        }

        // Overall ranking across all criteria
        $overallLeaderboard = \Illuminate\Support\Facades\DB::table('scores')
            ->join('participants', 'scores.participant_id', '=', 'participants.id')
            ->join('criteria', 'scores.criteria_id', '=', 'criteria.id')
            ->where('scores.event_id', $event->id)
            ->select(
                'participants.id',
                'participants.name',
                'participants.contestant_number',
                \Illuminate\Support\Facades\DB::raw('SUM(scores.score * criteria.weight) / SUM(criteria.weight) AS weighted_avg'),
                \Illuminate\Support\Facades\DB::raw('SUM(scores.score) AS total_raw_score')
            )
            ->groupBy('participants.id', 'participants.name', 'participants.contestant_number')
            ->orderByDesc('weighted_avg')
            ->get();

        $rank = 1;
        foreach ($overallLeaderboard as $i => $entry) {
            if ($i > 0 && $entry->weighted_avg < $overallLeaderboard[$i - 1]->weighted_avg) {
                $rank = $i + 1;
            }
            $entry->rank = $rank;
        }
        
        return view('admin.events.breakdown', compact('event', 'breakdown', 'overallLeaderboard'));
    }
}
