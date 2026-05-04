<?php

class ParticipantController extends Controller
{
    private function checkAuth()
    {
        if (!auth() || !auth()->isAdmin()) {
            $this->redirect(url('/admin/login'));
        }
    }

    public function index($eventId)
    {
        $this->checkAuth();
        $event = Event::scopedFindOrFail($eventId);
        
        $participants = $event->participants();
        usort($participants, fn($a, $b) => $a->contestant_number <=> $b->contestant_number);
        
        return $this->view('admin.participants.index', compact('event', 'participants'));
    }

    public function store($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);

        $name = $_POST['name'] ?? '';
        $contestant_number = $_POST['contestant_number'] ?? null;

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        Participant::create([
            'event_id' => $event->id,
            'name' => $name,
            'contestant_number' => $contestant_number ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        with('success', 'Participant added successfully!');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function update($eventId, $participantId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);
        $participant = Participant::findOrFail($participantId);
        
        if ($participant->event_id != $event->id) {
            die("Unauthorized");
        }

        $name = $_POST['name'] ?? '';
        $contestant_number = $_POST['contestant_number'] ?? null;

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        $participant->update([
            'name' => $name,
            'contestant_number' => $contestant_number ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        with('success', 'Participant updated!');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function destroy($eventId, $participantId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);
        $participant = Participant::findOrFail($participantId);
        
        if ($participant->event_id != $event->id) {
            die("Unauthorized");
        }

        $participant->delete();
        with('success', 'Participant removed.');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
