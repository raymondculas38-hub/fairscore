<?php

// ─── Root redirect ─────────────────────────────────────────────────────────
$router->get('/', function() {
    redirect(url('/admin/login'));
});
$router->get('/login', function() {
    redirect(url('/admin/login'));
});

// ─── Auth ──────────────────────────────────────────────────────────────────
$router->get('/admin/login', ['AuthController', 'showAdminLogin']);
$router->get('/admin/register', ['AuthController', 'showAdminRegister']);
$router->post('/admin/register', ['AuthController', 'registerAdmin']);
$router->get('/judge/login', ['AuthController', 'showJudgeLogin']);
$router->post('/login', ['AuthController', 'login']);
$router->post('/logout', ['AuthController', 'logout']);

// ─── Google OAuth ──────────────────────────────────────────────────────────
$router->get('/auth/google', ['GoogleAuthController', 'redirectToGoogle']);
$router->get('/auth/google/callback', ['GoogleAuthController', 'handleGoogleCallback']);

// ─── Public Leaderboard ────────────────────────────────────────────────────
$router->get('/live/{event}', ['LeaderboardController', 'show']);

// ─── Admin Portal ──────────────────────────────────────────────────────────
$router->get('/admin/dashboard', ['AdminController', 'dashboard']);
$router->post('/admin/create-account', ['AdminController', 'createAccount']);

// Events
$router->get('/admin/events', ['EventController', 'index']);
$router->get('/admin/events/create', ['EventController', 'create']);
$router->post('/admin/events', ['EventController', 'store']);
$router->get('/admin/events/{event}/edit', ['EventController', 'edit']);
$router->post('/admin/events/{event}', ['EventController', 'update']); // form method spoofing workaround or real POST
$router->get('/admin/events/{event}/delete', ['EventController', 'destroy']); // using GET to delete for simplicity since we remove Blade form spoofing
$router->post('/admin/events/{event}/delete', ['EventController', 'destroy']);
$router->get('/admin/events/{event}/breakdown', ['EventController', 'breakdown']);
$router->post('/admin/events/{event}/toggle', ['EventController', 'toggleStatus']);
$router->post('/admin/events/{event}/broadcast-pin', ['EventController', 'broadcastPin']);
$router->post('/admin/events/{event}/remind/{judge}', ['AdminController', 'remindJudge']);

// Participants (nested under event)
$router->get('/admin/events/{event}/participants', ['ParticipantController', 'index']);
$router->post('/admin/events/{event}/participants', ['ParticipantController', 'store']);
$router->post('/admin/events/{event}/participants/{participant}', ['ParticipantController', 'update']);
$router->get('/admin/events/{event}/participants/{participant}/delete', ['ParticipantController', 'destroy']);
$router->post('/admin/events/{event}/participants/{participant}/delete', ['ParticipantController', 'destroy']);

// Criteria (nested under event)
$router->get('/admin/events/{event}/criteria', ['CriteriaController', 'index']);
$router->post('/admin/events/{event}/criteria', ['CriteriaController', 'store']);
$router->post('/admin/events/{event}/criteria/{criteria}', ['CriteriaController', 'update']);
$router->get('/admin/events/{event}/criteria/{criteria}/delete', ['CriteriaController', 'destroy']);
$router->post('/admin/events/{event}/criteria/{criteria}/delete', ['CriteriaController', 'destroy']);

// Judges
$router->get('/admin/judges', ['JudgeController', 'index']);
$router->post('/admin/judges', ['JudgeController', 'store']);
$router->post('/admin/judges/{judge}', ['JudgeController', 'update']);
$router->get('/admin/judges/{judge}/delete', ['JudgeController', 'destroy']);
$router->post('/admin/judges/{judge}/delete', ['JudgeController', 'destroy']);

// Settings
$router->get('/admin/settings', ['SettingController', 'index']);
$router->post('/admin/settings', ['SettingController', 'update']);
$router->post('/admin/settings/factory-reset', ['SettingController', 'factoryReset']);

// Scoreboard
$router->get('/admin/scoreboard', ['ScoreboardController', 'index']);
$router->get('/admin/scoreboard/{event}', ['ScoreboardController', 'show']);
$router->post('/admin/scoreboard/{event}/set-display', ['ScoreboardController', 'setDisplay']);

// ─── Judge Portal ──────────────────────────────────────────────────────────
$router->get('/judge/dashboard', ['ScoringController', 'dashboard']);
$router->post('/judge/notifications/read', ['ScoringController', 'markNotificationsRead']);
$router->get('/judge/notifications/check', ['ScoringController', 'checkNotifications']);
$router->get('/judge/reminder/check', ['ScoringController', 'checkReminder']);
$router->post('/judge/notifications/{id}/delete', ['ScoringController', 'deleteNotification']); // method spoof
$router->get('/judge/event/{event}/pin', ['ScoringController', 'pinEntry']);
$router->post('/judge/event/{event}/pin', ['ScoringController', 'verifyPin']);
$router->post('/judge/event/{event}/leave', ['ScoringController', 'leaveEvent']);
$router->get('/judge/event/{event}/score', ['ScoringController', 'score']);
$router->post('/judge/event/{event}/score', ['ScoringController', 'submitScore']);
