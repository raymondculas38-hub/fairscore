<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criteria;
use App\Models\Event;
use Illuminate\Http\Request;

class CriteriaController extends Controller
{
    public function index(Event $event)
    {
        $criteria = $event->criteria()->get();
        return view('admin.criteria.index', compact('event', 'criteria'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'max_score' => 'required|numeric|min:1|max:1000',
            'weight'    => 'required|numeric|min:0.01|max:100',
        ]);

        $validated['event_id'] = $event->id;
        Criteria::create($validated);

        return back()->with('success', 'Criteria added successfully!');
    }

    public function update(Request $request, Event $event, Criteria $criteria)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'max_score' => 'required|numeric|min:1|max:1000',
            'weight'    => 'required|numeric|min:0.01|max:100',
        ]);

        $criteria->update($validated);
        return back()->with('success', 'Criteria updated!');
    }

    public function destroy(Event $event, Criteria $criteria)
    {
        $criteria->delete();
        return back()->with('success', 'Criteria removed.');
    }
}
