<?php

class CriteriaController extends Controller
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
        $criteria = $event->criteria();
        
        return $this->view('admin.criteria.index', compact('event', 'criteria'));
    }

    public function store($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);

        $name = $_POST['name'] ?? '';
        $max_score = $_POST['max_score'] ?? '';
        $weight = $_POST['weight'] ?? '';

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';
        if (!is_numeric($max_score) || $max_score < 1 || $max_score > 1000) $errors['max_score'] = 'Max score must be between 1 and 1000.';
        if (!is_numeric($weight) || $weight < 0.01 || $weight > 100) $errors['weight'] = 'Weight must be between 0.01 and 100.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        Criteria::create([
            'event_id' => $event->id,
            'name' => $name,
            'max_score' => $max_score,
            'weight' => $weight,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        with('success', 'Criteria added successfully!');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function update($eventId, $criteriaId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);
        $criteria = Criteria::findOrFail($criteriaId);
        
        if ($criteria->event_id != $event->id) {
            die("Unauthorized");
        }

        $name = $_POST['name'] ?? '';
        $max_score = $_POST['max_score'] ?? '';
        $weight = $_POST['weight'] ?? '';

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';
        if (!is_numeric($max_score) || $max_score < 1 || $max_score > 1000) $errors['max_score'] = 'Max score must be between 1 and 1000.';
        if (!is_numeric($weight) || $weight < 0.01 || $weight > 100) $errors['weight'] = 'Weight must be between 0.01 and 100.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        $criteria->update([
            'name' => $name,
            'max_score' => $max_score,
            'weight' => $weight,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        with('success', 'Criteria updated!');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function destroy($eventId, $criteriaId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);
        $criteria = Criteria::findOrFail($criteriaId);
        
        if ($criteria->event_id != $event->id) {
            die("Unauthorized");
        }

        $criteria->delete();
        with('success', 'Criteria removed.');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
