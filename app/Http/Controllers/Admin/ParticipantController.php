<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Event $event)
    {
        $participants = $event->participants()->orderBy('contestant_number')->get();
        return view('admin.participants.index', compact('event', 'participants'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'contestant_number' => 'nullable|integer|min:1',
        ]);

        $validated['event_id'] = $event->id;
        Participant::create($validated);

        return back()->with('success', 'Participant added successfully!');
    }

    public function update(Request $request, Event $event, Participant $participant)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'contestant_number' => 'nullable|integer|min:1',
        ]);

        $participant->update($validated);
        return back()->with('success', 'Participant updated!');
    }

    public function destroy(Event $event, Participant $participant)
    {
        $participant->delete();
        return back()->with('success', 'Participant removed.');
    }
}
