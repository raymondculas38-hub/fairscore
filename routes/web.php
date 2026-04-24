<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\CriteriaController;
use App\Http\Controllers\Admin\JudgeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ScoreboardController;
use App\Http\Controllers\Judge\ScoringController;

use App\Http\Controllers\GoogleAuthController;

// ─── Root redirect ─────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('admin.login'));
Route::get('/login', fn() => redirect()->route('admin.login'))->name('login');

// ─── Auth ──────────────────────────────────────────────────────────────────
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::get('/admin/register', [AuthController::class, 'showAdminRegister'])->name('admin.register');
Route::post('/admin/register', [AuthController::class, 'registerAdmin'])->name('admin.register.post');
Route::get('/judge/login', [AuthController::class, 'showJudgeLogin'])->name('judge.login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Google OAuth ──────────────────────────────────────────────────────────
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// ─── Public Leaderboard ────────────────────────────────────────────────────
Route::get('/live/{event}', [LeaderboardController::class, 'show'])->name('leaderboard.show');

// ─── Admin Portal ──────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/create-account', [AdminController::class, 'createAccount'])->name('create-account');

    // Events
    Route::get('/events',                [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create',         [EventController::class, 'create'])->name('events.create');
    Route::post('/events',               [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit',   [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}',        [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}',     [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{event}/breakdown', [EventController::class, 'breakdown'])->name('events.breakdown');
    Route::post('/events/{event}/toggle',[EventController::class, 'toggleStatus'])->name('events.toggle');
    Route::post('/events/{event}/remind/{judge}', [AdminController::class, 'remindJudge'])->name('events.remind');

    // Participants (nested under event)
    Route::get('/events/{event}/participants',                       [ParticipantController::class, 'index'])->name('participants.index');
    Route::post('/events/{event}/participants',                      [ParticipantController::class, 'store'])->name('participants.store');
    Route::put('/events/{event}/participants/{participant}',         [ParticipantController::class, 'update'])->name('participants.update');
    Route::delete('/events/{event}/participants/{participant}',      [ParticipantController::class, 'destroy'])->name('participants.destroy');

    // Criteria (nested under event)
    Route::get('/events/{event}/criteria',                   [CriteriaController::class, 'index'])->name('criteria.index');
    Route::post('/events/{event}/criteria',                  [CriteriaController::class, 'store'])->name('criteria.store');
    Route::put('/events/{event}/criteria/{criteria}',        [CriteriaController::class, 'update'])->name('criteria.update');
    Route::delete('/events/{event}/criteria/{criteria}',     [CriteriaController::class, 'destroy'])->name('criteria.destroy');

    // Judges
    Route::get('/judges',          [JudgeController::class, 'index'])->name('judges.index');
    Route::post('/judges',         [JudgeController::class, 'store'])->name('judges.store');
    Route::put('/judges/{judge}',  [JudgeController::class, 'update'])->name('judges.update');
    Route::delete('/judges/{judge}',[JudgeController::class, 'destroy'])->name('judges.destroy');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/factory-reset', [SettingController::class, 'factoryReset'])->name('settings.factory_reset');

    // ── Scoreboard Module (separate from Events) ────────────────────────
    Route::get('/scoreboard',                        [ScoreboardController::class, 'index'])->name('scoreboard.index');
    Route::get('/scoreboard/{event}',                [ScoreboardController::class, 'show'])->name('scoreboard.show');
    Route::post('/scoreboard/{event}/set-display',   [ScoreboardController::class, 'setDisplay'])->name('scoreboard.setDisplay');
});

// ─── Judge Portal ──────────────────────────────────────────────────────────
Route::prefix('judge')->name('judge.')->middleware(['auth', 'judge'])->group(function () {
    Route::get('/dashboard',                         [ScoringController::class, 'dashboard'])->name('dashboard');
    Route::post('/notifications/read',               [ScoringController::class, 'markNotificationsRead'])->name('notifications.markAllRead');
    Route::get('/notifications/check',               [ScoringController::class, 'checkNotifications'])->name('notifications.check');
    Route::delete('/notifications/{id}',             [ScoringController::class, 'deleteNotification'])->name('notifications.delete');
    Route::get('/event/{event}/pin',                 [ScoringController::class, 'pinEntry'])->name('score.pin');
    Route::post('/event/{event}/pin',                [ScoringController::class, 'verifyPin'])->name('score.verify');
    Route::get('/event/{event}/score',               [ScoringController::class, 'score'])->name('score');
    Route::post('/event/{event}/score',              [ScoringController::class, 'submitScore'])->name('score.submit');
});
