<?php

class JudgeController extends Controller
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
        $judges = User::where('role', '=', 'JUDGE');
        
        foreach ($judges as $judge) {
            $judge->events = $judge->events();
        }
        
        usort($judges, fn($a, $b) => strtotime($b->created_at) - strtotime($a->created_at));
        
        return $this->view('admin.judges.index', compact('judges'));
    }

    public function store()
    {
        $this->checkAuth();
        check_csrf();

        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';
        if (empty($username)) $errors['username'] = 'Username is required.';
        if (empty($password) || strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';

        if (empty($errors)) {
            if (User::firstWhere('username', '=', $username)) {
                $errors['username'] = 'This username is already taken.';
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
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'JUDGE',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        with('success', 'Judge account created!');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function update($judgeId)
    {
        $this->checkAuth();
        check_csrf();
        
        $judge = User::findOrFail($judgeId);
        
        if ($judge->role !== 'JUDGE') {
            die("Unauthorized");
        }

        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (empty($name)) $errors['name'] = 'Name is required.';
        if (empty($username)) $errors['username'] = 'Username is required.';
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }
        
        $existing = User::firstWhere('username', '=', $username);
        if ($existing && $existing->id !== $judge->id) {
            $_SESSION['errors'] = ['username' => 'This username is already taken.'];
            $_SESSION['old'] = $_POST;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        $data = [
            'name' => $name,
            'username' => $username,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($password)) {
            if (strlen($password) < 6) {
                $_SESSION['errors'] = ['password' => 'Password must be at least 6 characters.'];
                $_SESSION['old'] = $_POST;
                return $this->redirect($_SERVER['HTTP_REFERER']);
            }
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $judge->update($data);
        with('success', 'Judge updated!');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function destroy($judgeId)
    {
        $this->checkAuth();
        check_csrf();
        
        $judge = User::findOrFail($judgeId);
        if ($judge->role !== 'JUDGE') {
            die("Unauthorized");
        }
        
        $judge->delete();
        with('success', 'Judge account removed.');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
