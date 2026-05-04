<?php

class GoogleAuthController extends Controller
{
    private function getClient()
    {
        if (!class_exists('Google\Client')) {
            die("Google API Client Library for PHP is not installed. Run 'composer require google/apiclient'");
        }
        
        $client = new Google\Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URL'] ?? url('auth/google/callback'));
        $client->addScope("email");
        $client->addScope("profile");
        return $client;
    }

    public function redirectToGoogle()
    {
        $client = $this->getClient();
        $authUrl = $client->createAuthUrl();
        $this->redirect($authUrl);
    }

    public function handleGoogleCallback()
    {
        try {
            $client = $this->getClient();
            
            if (!isset($_GET['code'])) {
                return $this->redirect(url('admin/login'));
            }
            
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (isset($token['error'])) {
                throw new Exception($token['error_description'] ?? 'Failed to get access token');
            }
            
            $client->setAccessToken($token['access_token']);
            $googleOauth = new Google\Service\Oauth2($client);
            $googleUser = $googleOauth->userinfo->get();
            
            $googleId = $googleUser->id;
            $googleEmail = $googleUser->email;
            $googleName = $googleUser->name;
            
            error_log("Google OAuth callback: id=$googleId, email=$googleEmail, name=$googleName");
            
            $existingUser = User::firstWhere('google_id', '=', $googleId);
            if ($existingUser) {
                if (!$existingUser->isAdmin()) {
                    $existingUser->update(['role' => 'ADMIN']);
                }
                $this->loginUser($existingUser);
                return $this->redirectBasedOnRole($existingUser);
            }
            
            $userByEmail = User::firstWhere('email', '=', $googleEmail);
            if ($userByEmail) {
                $userByEmail->update([
                    'google_id' => $googleId,
                    'role' => 'ADMIN'
                ]);
                $this->loginUser($userByEmail);
                return $this->redirectBasedOnRole($userByEmail);
            }
            
            $usernamePrefix = explode('@', $googleEmail)[0];
            $username = $usernamePrefix;
            
            while (User::firstWhere('username', '=', $username)) {
                $username = $usernamePrefix . rand(1000, 9999);
            }
            
            $newUser = User::create([
                'name' => $googleName,
                'email' => $googleEmail,
                'username' => $username,
                'google_id' => $googleId,
                'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                'role' => 'ADMIN',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->loginUser($newUser);
            return $this->redirectBasedOnRole($newUser);
            
        } catch (Exception $e) {
            error_log('Google Auth Error: ' . $e->getMessage());
            with('error', 'Google authentication failed. Please try again.');
            return $this->redirect(url('admin/login'));
        }
    }
    
    private function loginUser(User $user)
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
    }

    private function redirectBasedOnRole(User $user)
    {
        return $user->isAdmin()
            ? $this->redirect(url('admin/dashboard'))
            : $this->redirect(url('judge/dashboard'));
    }
}
