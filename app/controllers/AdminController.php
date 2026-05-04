<?php

class AdminController extends Controller
{
    public function dashboard()
    {
        // Enforce Admin Access
        if (!auth() || !auth()->isAdmin()) {
            return $this->redirect(url('/admin/login'));
        }

        $allEvents = Event::scopedAll();
        foreach ($allEvents as $event) {
            $event->participants = $event->participants();
            $event->judges = $event->judges();
            $event->scores = $event->scores();
            $event->participants_count = count($event->participants);
            $event->judges_count = count($event->judges);
        }
        
        $stats = [
            'total_events' => count($allEvents),
            'live_events' => count(array_filter($allEvents, fn($e) => $e->status === 'live')),
            'total_judges' => count(User::where('role', '=', 'JUDGE')), // Global count of judges
            'total_scores' => count(Score::all()), // Should probably scope this, but kept as is
        ];

        // Fetch active events (upcoming, live)
        $activeEvents = array_filter($allEvents, fn($e) => in_array($e->status, ['upcoming', 'live']));
        usort($activeEvents, fn($a, $b) => strtotime($b->created_at) - strtotime($a->created_at));
        $activeEvents = array_slice($activeEvents, 0, 5);

        // Fetch live events with full relationships
        $liveEvents = array_filter($allEvents, fn($e) => $e->status === 'live');
        
        // Relations already populated above

        return $this->view('admin.dashboard', compact('stats', 'activeEvents', 'liveEvents'));
    }

    public function createAccount()
    {
        if (!auth() || !auth()->isAdmin()) {
            return $this->redirect(url('/admin/login'));
        }
        
        check_csrf();

        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirmation = $_POST['password_confirmation'] ?? '';

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';
        if (empty($username)) $errors['username'] = 'Username is required.';
        if (empty($email)) $errors['email'] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address.';
        if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
        if ($password !== $password_confirmation) $errors['password_confirmation'] = 'Passwords do not match.';

        if (empty($errors)) {
            if (User::firstWhere('username', '=', $username)) {
                $errors['username'] = 'This username is already taken.';
            }
            if (User::firstWhere('email', '=', $email)) {
                $errors['email'] = 'This email is already registered.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'ADMIN',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        with('success', "Admin account \"{$username}\" created successfully.");
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function remindJudge($eventId, $judgeId)
    {
        if (!auth() || !auth()->isAdmin()) {
            return $this->redirect(url('/admin/login'));
        }

        $event = Event::scopedFindOrFail($eventId);
        $judge = User::findOrFail($judgeId);

        // Persist reminder in DB — judge's browser polls for this every 2s
        $db = Model::getDb();
        $stmt = $db->prepare(
            "INSERT INTO judge_reminders (judge_id, event_id, event_name, seen, created_at) VALUES (?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$judge->id, $event->id, $event->name]);

        // Also try Socket.IO as best-effort (silently ignored if server is off)
        $data = ['judge_id' => $judge->id, 'event_id' => $event->id, 'event_name' => $event->name];
        $options = ['http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'timeout' => 1,
            'ignore_errors' => true,
        ]];
        @file_get_contents('http://localhost:3000/api/emit-reminder', false, stream_context_create($options));

        with('success', "Reminder sent to {$judge->name}. They will see a full-screen alert.");
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
