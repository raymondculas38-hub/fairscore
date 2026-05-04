<?php

class ScoringController extends Controller
{
    private function checkAuth()
    {
        if (!auth() || !auth()->isJudge()) {
            $this->redirect(url('/judge/login'));
        }
    }

    public function dashboard()
    {
        $this->checkAuth();
        $judge = auth();
        $events = $judge->events();
        
        foreach ($events as $event) {
            $event->participants_count = count($event->participants());
        }
        
        return $this->view('judge.dashboard', compact('events'));
    }

    public function markNotificationsRead()
    {
        $this->checkAuth();
        check_csrf();
        
        $db = Model::getDb();
        // Only mark PIN broadcast notifications as read
        $stmt = $db->prepare("UPDATE notifications SET read_at = NOW() WHERE notifiable_id = ? AND notifiable_type = ? AND type = ? AND read_at IS NULL");
        $stmt->execute([auth()->id, 'App\Models\User', 'App\Notifications\PinBroadcast']);
        
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function checkNotifications()
    {
        $this->checkAuth();
        
        $db = Model::getDb();
        $notifiableType = 'App\Models\User';
        $pinType        = 'App\Notifications\PinBroadcast';
        
        // Only count PIN broadcast notifications
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE notifiable_id = ? AND notifiable_type = ? AND type = ? AND read_at IS NULL");
        $stmt->execute([auth()->id, $notifiableType, $pinType]);
        $unreadCount = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT * FROM notifications WHERE notifiable_id = ? AND notifiable_type = ? AND type = ? AND read_at IS NULL ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([auth()->id, $notifiableType, $pinType]);
        $latestObj = $stmt->fetch();
        
        header('Content-Type: application/json');
        
        if ($latestObj) {
            $data = json_decode($latestObj->data, true);
            echo json_encode([
                'unread_count' => $unreadCount,
                'latest' => [
                    'id' => $latestObj->id,
                    'event_name' => $data['event_name'] ?? '',
                    'message' => $data['message'] ?? '',
                    'description' => $data['description'] ?? '',
                    'created_at' => $latestObj->created_at
                ]
            ]);
        } else {
            echo json_encode(['unread_count' => $unreadCount, 'latest' => null]);
        }
        exit();
    }

    public function deleteNotification($id)
    {
        $this->checkAuth();
        check_csrf();
        
        $db = Model::getDb();
        $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND notifiable_id = ?");
        $stmt->execute([$id, auth()->id]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    public function checkReminder()
    {
        $this->checkAuth();

        $db = Model::getDb();
        // Fetch the oldest unseen reminder for this judge
        $stmt = $db->prepare(
            "SELECT * FROM judge_reminders WHERE judge_id = ? AND seen = 0 ORDER BY created_at ASC LIMIT 1"
        );
        $stmt->execute([auth()->id]);
        $reminder = $stmt->fetch(PDO::FETCH_OBJ);

        header('Content-Type: application/json');

        if ($reminder) {
            // Mark it as seen immediately so it won't fire again
            $upd = $db->prepare("UPDATE judge_reminders SET seen = 1 WHERE id = ?");
            $upd->execute([$reminder->id]);

            echo json_encode([
                'has_reminder' => true,
                'event_name'   => $reminder->event_name,
                'event_id'     => $reminder->event_id,
            ]);
        } else {
            echo json_encode(['has_reminder' => false]);
        }
        exit();
    }

    public function pinEntry($eventId)
    {
        $this->checkAuth();
        $event = Event::findOrFail($eventId);
        
        if (isset($_SESSION["event_pin_{$event->id}"]) && $_SESSION["event_pin_{$event->id}"] === $event->pin) {
            return $this->redirect(url("/judge/event/{$event->id}/score"));
        }
        
        return $this->view('judge.pin_entry', compact('event'));
    }

    public function verifyPin($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::findOrFail($eventId);
        $pin = $_POST['pin'] ?? '';
        
        if ($pin !== $event->pin) {
            $_SESSION['errors'] = ['pin' => 'Invalid PIN code. Please check your notifications.'];
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }
        
        $_SESSION["event_pin_{$event->id}"] = $event->pin;
        with('success', 'PIN accepted. You can now score the event.');
        return $this->redirect(url("/judge/event/{$event->id}/score"));
    }

    public function score($eventId)
    {
        $this->checkAuth();
        $event = Event::findOrFail($eventId);
        
        if (!isset($_SESSION["event_pin_{$event->id}"]) || $_SESSION["event_pin_{$event->id}"] !== $event->pin) {
            with('error', 'You must enter the event PIN to continue.');
            return $this->redirect(url("/judge/event/{$event->id}/pin"));
        }
        
        $judge = auth();
        
        $participants = $event->participants();
        usort($participants, fn($a, $b) => $a->contestant_number <=> $b->contestant_number);
        
        $criteria = $event->criteria();
        
        $scoresList = Score::where('event_id', '=', $event->id);
        $existingScores = [];
        foreach ($scoresList as $s) {
            if ($s->judge_id == $judge->id) {
                $existingScores[$s->participant_id . '_' . $s->criteria_id] = $s;
            }
        }
        
        return $this->view('judge.score', compact('event', 'participants', 'criteria', 'existingScores'));
    }

    public function submitScore($eventId)
    {
        $this->checkAuth();
        $event = Event::findOrFail($eventId);
        
        // JSON API endpoint
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        $participant_id = $input['participant_id'] ?? null;
        $criteria_id = $input['criteria_id'] ?? null;
        $scoreVal = $input['score'] ?? null;
        
        if (!$participant_id || !$criteria_id || !is_numeric($scoreVal) || $scoreVal < 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            exit();
        }
        
        $judge = auth();
        $criteria = Criteria::findOrFail($criteria_id);
        
        $scoreVal = min((float)$scoreVal, (float)$criteria->max_score);
        
        // updateOrCreate
        $db = Model::getDb();
        $stmt = $db->prepare("SELECT id FROM scores WHERE event_id = ? AND judge_id = ? AND participant_id = ? AND criteria_id = ?");
        $stmt->execute([$event->id, $judge->id, $participant_id, $criteria_id]);
        $existingId = $stmt->fetchColumn();
        
        if ($existingId) {
            $stmt = $db->prepare("UPDATE scores SET score = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$scoreVal, $existingId]);
        } else {
            $stmt = $db->prepare("INSERT INTO scores (event_id, judge_id, participant_id, criteria_id, score, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$event->id, $judge->id, $participant_id, $criteria_id, $scoreVal]);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'saved_score' => $scoreVal]);
        exit();
    }

    public function leaveEvent($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $db = Model::getDb();
        $stmt = $db->prepare("DELETE FROM event_judge WHERE event_id = ? AND judge_id = ?");
        $stmt->execute([$eventId, auth()->id]);
        
        with('success', 'You have successfully left the event.');
        return $this->redirect(url('/judge/dashboard'));
    }
}
