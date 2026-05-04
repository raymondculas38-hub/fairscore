<?php

class EventController extends Controller
{
    private function checkAuth()
    {
        if (!auth() || !auth()->isAdmin()) {
            $this->redirect(url('/admin/login'));
        }
    }

    public function index()
    {
        $this->checkAuth();
        $events = Event::scopedAll();
        
        // Add counts
        foreach ($events as $event) {
            $event->participants_count = count($event->participants());
            $event->criteria_count = count($event->criteria());
            $event->judges_count = count($event->judges());
        }
        
        // Sort latest
        usort($events, fn($a, $b) => strtotime($b->created_at) - strtotime($a->created_at));
        
        return $this->view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $this->checkAuth();
        return $this->view('admin.events.create');
    }

    public function store()
    {
        $this->checkAuth();
        check_csrf();

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $event_date = $_POST['event_date'] ?? '';
        $status = $_POST['status'] ?? '';
        $pin = $_POST['pin'] ?? '';

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';
        if (empty($status) || !in_array($status, ['upcoming', 'live', 'completed'])) $errors['status'] = 'Invalid status.';
        if (empty($pin) || strlen($pin) !== 6) $errors['pin'] = 'Pin must be exactly 6 characters.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect(url('/admin/events/create'));
        }

        Event::create([
            'admin_id' => auth()->id,
            'name' => $name,
            'description' => $description,
            'event_date' => $event_date ?: null,
            'status' => $status,
            'pin' => $pin,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        with('success', 'Event created successfully!');
        return $this->redirect(url('/admin/events'));
    }

    public function edit($eventId)
    {
        $this->checkAuth();
        $event = Event::scopedFindOrFail($eventId);
        
        $allJudges = User::where('role', '=', 'JUDGE');
        $assignedJudgesObjects = $event->judges();
        $assignedJudges = array_map(fn($j) => $j->id, $assignedJudgesObjects);
        
        return $this->view('admin.events.edit', compact('event', 'allJudges', 'assignedJudges'));
    }

    public function update($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $event_date = $_POST['event_date'] ?? '';
        $status = $_POST['status'] ?? '';
        $pin = $_POST['pin'] ?? '';
        $judges = $_POST['judges'] ?? [];

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';
        if (empty($status) || !in_array($status, ['upcoming', 'live', 'completed'])) $errors['status'] = 'Invalid status.';
        if (empty($pin) || strlen($pin) !== 6) $errors['pin'] = 'Pin must be exactly 6 characters.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect(url('/admin/events/' . $eventId . '/edit'));
        }

        $event->update([
            'name' => $name,
            'description' => $description,
            'event_date' => $event_date ?: null,
            'status' => $status,
            'pin' => $pin,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $event->syncJudges($judges);

        with('success', 'Event updated successfully!');
        return $this->redirect(url('/admin/events'));
    }

    public function destroy($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);
        $event->delete();
        
        with('success', 'Event deleted.');
        return $this->redirect(url('/admin/events'));
    }

    public function toggleStatus($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);
        
        $next = 'upcoming';
        if ($event->status === 'upcoming') $next = 'live';
        elseif ($event->status === 'live') $next = 'completed';
        
        $event->update(['status' => $next, 'updated_at' => date('Y-m-d H:i:s')]);

        with('success', "Event status changed to \"{$next}\".");
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function breakdown($eventId)
    {
        $this->checkAuth();
        $event = Event::scopedFindOrFail($eventId);
        
        $criteriaList = $event->criteria();
        $judgesList = $event->judges();
        $participantsList = $event->participants();
        $scoresList = $event->scores();
        
        $breakdown = [];
        
        foreach ($criteriaList as $criterion) {
            $catData = [
                'criterion' => $criterion,
                'participants' => []
            ];
            
            foreach ($participantsList as $participant) {
                // Filter scores for this participant and criterion
                $pScores = array_filter($scoresList, fn($s) => $s->participant_id == $participant->id && $s->criteria_id == $criterion->id);
                
                $judgeScores = [];
                $total = 0;
                
                foreach ($judgesList as $judge) {
                    $s = null;
                    foreach ($pScores as $scoreObj) {
                        if ($scoreObj->judge_id == $judge->id) {
                            $s = $scoreObj;
                            break;
                        }
                    }
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
        $stmt = Model::getDb()->prepare("
            SELECT 
                p.id,
                p.name,
                p.contestant_number,
                COALESCE(SUM(s.score * c.weight) / NULLIF(SUM(c.weight), 0), 0) AS weighted_avg,
                COALESCE(SUM(s.score), 0) AS total_raw_score
            FROM participants p
            LEFT JOIN scores s ON s.participant_id = p.id AND s.event_id = ?
            LEFT JOIN criteria c ON s.criteria_id = c.id
            WHERE p.event_id = ?
            GROUP BY p.id, p.name, p.contestant_number
            ORDER BY weighted_avg DESC, total_raw_score DESC
        ");
        $stmt->execute([$event->id, $event->id]);
        $overallLeaderboard = $stmt->fetchAll(PDO::FETCH_OBJ);

        $rank = 1;
        foreach ($overallLeaderboard as $i => $entry) {
            if ($i > 0 && $entry->weighted_avg < $overallLeaderboard[$i - 1]->weighted_avg) {
                $rank = $i + 1;
            }
            $entry->rank = $rank;
        }
        
        return $this->view('admin.events.breakdown', compact('event', 'breakdown', 'overallLeaderboard'));
    }

    public function broadcastPin($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);
        $judges = $event->judges();
        
        if (empty($judges)) {
            with('error', 'No judges are assigned to this event.');
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }
        
        $db = Model::getDb();
        // Use placeholders for ALL values to avoid backslash escaping issues
        $stmt = $db->prepare(
            "INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
        );
        
        $type           = 'App\Notifications\PinBroadcast';
        $notifiableType = 'App\Models\User';
        $successCount   = 0;
        
        foreach ($judges as $judge) {
            // Generate a UUID v4
            $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            $notifData = json_encode([
                'event_name'  => $event->name,
                'message'     => "Event PIN: {$event->pin}",
                'description' => "Use this PIN to access the scoring module for {$event->name}."
            ]);
            
            $stmt->execute([$id, $type, $notifiableType, $judge->id, $notifData]);
            $successCount++;
        }
        
        with('success', "PIN broadcasted to {$successCount} judge(s). They will see it in their notification bell.");
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
