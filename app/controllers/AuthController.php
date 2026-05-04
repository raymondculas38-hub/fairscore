<?php

class AuthController extends Controller
{
    public function showAdminLogin()
    {
        if (auth()) {
            return auth()->isAdmin()
                ? $this->redirect(url('/admin/dashboard'))
                : $this->redirect(url('/judge/dashboard'));
        }
        return $this->view('auth.admin_login');
    }

    public function showAdminRegister()
    {
        if (auth()) {
            return auth()->isAdmin()
                ? $this->redirect(url('/admin/dashboard'))
                : $this->redirect(url('/judge/dashboard'));
        }
        return $this->view('auth.admin_register');
    }

    public function registerAdmin()
    {
        check_csrf();
        
        $name = $_POST['name'] ?? '';
        $address = $_POST['address'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $errors = [];
        
        if (empty($name)) $errors['name'] = 'Name is required.';
        if (empty($address)) $errors['address'] = 'Address is required.';
        if (empty($username)) $errors['username'] = 'Username is required.';
        if (strlen($password) < 8 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must be at least 8 characters long and contain both letters and numbers.';
        }
        
        if (empty($errors)) {
            $existing = User::firstWhere('username', '=', $username) ?: User::firstWhere('email', '=', $username);
            if ($existing) {
                $errors['username'] = 'This username is already taken.';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect(url('/admin/register'));
        }
        
        User::create([
            'name' => $name,
            'address' => $address,
            'username' => $username,
            'email' => $username, // fallback
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'ADMIN',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        with('success', 'Admin account created! You can now log in.');
        return $this->redirect(url('/admin/login'));
    }

    public function showJudgeLogin()
    {
        if (auth()) {
            return auth()->isAdmin()
                ? $this->redirect(url('/admin/dashboard'))
                : $this->redirect(url('/judge/dashboard'));
        }
        return $this->view('auth.judge_login');
    }

    public function login()
    {
        check_csrf();
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $intended_role = $_POST['intended_role'] ?? '';
        
        $errors = [];
        if (empty($username)) $errors['username'] = 'Username is required.';
        if (empty($password)) $errors['password'] = 'Password is required.';
        
        if ($intended_role === 'admin') {
            if (strlen($password) < 8 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $errors['password'] = 'Password must contain both letters and numbers, and be at least 8 characters.';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }
        
        $user = User::firstWhere('username', '=', $username) ?: User::firstWhere('email', '=', $username);
        
        if ($user && password_verify($password, $user->password)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user->id;
            
            return $user->isAdmin()
                ? $this->redirect(url('/admin/dashboard'))
                : $this->redirect(url('/judge/dashboard'));
        }
        
        $_SESSION['errors'] = ['username' => 'Invalid username or password.'];
        $_SESSION['old'] = $_POST;
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function logout()
    {
        check_csrf();
        $role = auth() ? auth()->role : null;
        
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);
        
        if ($role === 'JUDGE') {
            return $this->redirect(url('/judge/login'));
        }
        
        return $this->redirect(url('/admin/login'));
    }
}
