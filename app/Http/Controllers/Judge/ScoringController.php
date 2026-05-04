<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Score;
use Illuminate\Http\Request;

class ScoringController extends Controller
{
    public function dashboard()
    {
        $judge  = auth()->user();
        $events = $judge->events()->withCount('participants')->get();
        return view('judge.dashboard', compact('events'));
    }

    public function markNotificationsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }

    public function checkNotifications(Request $request)
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();
        $latest = $user->unreadNotifications()->latest()->first();
        
        return response()->json([
            'unread_count' => $unreadCount,
            'latest'       => $latest ? [
                'id'          => $latest->id,   
                'event_name'  => $latest->data['event_name'] ?? '',
                'message'     => $latest->data['message'] ?? '',
                'description' => $latest->data['description'] ?? '',
                'created_at'  => $latest->created_at->diffForHumans()
            ] : null,
        ]);
    }

    public function deleteNotification($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function pinEntry(Event $event)
    {
        // Check if already authenticated via PIN
        if (session()->has("event_pin_{$event->id}") && session("event_pin_{$event->id}") === $event->pin) {
            return redirect()->route('judge.score', $event);
        }
        
        return view('judge.pin_entry', compact('event'));
    }

    public function verifyPin(Request $request, Event $event)
    {
        $request->validate(['pin' => 'required|string|size:6']);

        if ($request->pin !== $event->pin) {
            return back()->withErrors(['pin' => 'Invalid PIN code. Please check your notifications.']);
        }

        session(["event_pin_{$event->id}" => $event->pin]);

        return redirect()->route('judge.score', $event)->with('success', 'PIN accepted. You can now score the event.');
    }

    public function score(Event $event)
    {
        // PIN protection check
        if (!session()->has("event_pin_{$event->id}") || session("event_pin_{$event->id}") !== $event->pin) {
            return redirect()->route('judge.score.pin', $event)->with('error', 'You must enter the event PIN to continue.');
        }

        $judge        = auth()->user();
        $participants = $event->participants()->orderBy('contestant_number')->get();
        $criteria     = $event->criteria()->get();

        // Load this judge's existing scores indexed by participant_id + criteria_id
        $existingScores = Score::where('event_id', $event->id)
            ->where('judge_id', $judge->id)
            ->get()
            ->keyBy(fn($s) => $s->participant_id . '_' . $s->criteria_id);

        return view('judge.score', compact('event', 'participants', 'criteria', 'existingScores'));
    }

    public function submitScore(Request $request, Event $event)
    {
        $validated = $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'criteria_id'    => 'required|exists:criteria,id',
            'score'          => 'required|numeric|min:0',
        ]);

        $judge    = auth()->user();
        $criteria = $event->criteria()->findOrFail($validated['criteria_id']);

        // Clamp score to max_score
        $score = min((float) $validated['score'], (float) $criteria->max_score);

        Score::updateOrCreate(
            [
                'judge_id'       => $judge->id,
                'participant_id' => $validated['participant_id'],
                'criteria_id'    => $validated['criteria_id'],
            ],
            [
                'event_id' => $event->id,
                'score'    => $score,
            ]
        );

        return response()->json(['success' => true, 'saved_score' => $score]);
    }
}
