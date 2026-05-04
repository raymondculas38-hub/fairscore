<?php $__sections['title'] = 'Judges'; ?>
<?php $__sections['page-title'] = 'Judges'; ?>
<?php $__sections['page-subtitle'] = 'Manage judge accounts'; ?>

<?php ob_start(); ?>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3 animate-fade-in-up">

    <!-- Add judge form -->
    <div class="glass-card p-5 h-fit">
        <h3 class="text-sm font-bold mb-4" style="color:#e2e8f0;">Create Judge Account</h3>
        <form method="POST" action="<?= htmlspecialchars((string)(url('/admin/judges'))) ?>">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Full Name <span style="color:#f87171;">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars((string)(old('name'))) ?>" class="form-input" placeholder="e.g. Judge Dela Cruz" required>
                    <?php if(!empty($_SESSION['errors']['name'])): ?><p class="text-xs mt-1" style="color:#f87171;"><?= htmlspecialchars((string)($_SESSION['errors']['name'])) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Username <span style="color:#f87171;">*</span></label>
                    <input type="text" name="username" value="<?= htmlspecialchars((string)(old('username'))) ?>" class="form-input" placeholder="e.g. judge1" required>
                    <?php if(!empty($_SESSION['errors']['username'])): ?><p class="text-xs mt-1" style="color:#f87171;"><?= htmlspecialchars((string)($_SESSION['errors']['username'])) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Password <span style="color:#f87171;">*</span></label>
                    <input type="password" name="password" class="form-input" placeholder="Min. 6 characters" required>
                    <?php if(!empty($_SESSION['errors']['password'])): ?><p class="text-xs mt-1" style="color:#f87171;"><?= htmlspecialchars((string)($_SESSION['errors']['password'])) ?></p><?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full justify-center mt-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                Create Judge
            </button>
        </form>
    </div>

    <!-- Judges list -->
    <div class="lg:col-span-2 glass-card">
        <div class="px-5 py-4 border-b" style="border-color:rgba(255,255,255,0.06);">
            <h3 class="text-sm font-bold" style="color:#e2e8f0;">All Judges (<?= count($judges) ?>)</h3>
        </div>
        <div class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
            <?php if(empty($judges)): ?>
                <div class="py-12 text-center" style="color:#334155;">
                    <div class="text-4xl mb-3">🧑‍⚖️</div>
                    <p class="text-sm">No judges yet. Create one on the left.</p>
                </div>
            <?php else: ?>
                <?php foreach($judges as $judge): ?>
                    <div class="px-5 py-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0" style="background:rgba(99,102,241,0.2);color:#818cf8;">
                                <?= htmlspecialchars((string)(strtoupper(substr($judge->name, 0, 2)))) ?>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold" style="color:#e2e8f0;"><?= htmlspecialchars((string)($judge->name)) ?></div>
                                <div class="text-xs" style="color:#475569;">
                                    @<?= htmlspecialchars((string)($judge->username)) ?>
                                    <?php if(!empty($judge->events)): ?>
                                        · Assigned to <?= count($judge->events) ?> event(s)
                                    <?php else: ?>
                                        · No events assigned
                                    <?php endif; ?>
                                </div>
                            </div>

                            <form method="POST" action="<?= htmlspecialchars((string)(url('/admin/judges/' . $judge->id . '/delete'))) ?>" onsubmit="return confirm('Remove this judge account and all their scores?')">
                                <?= csrf_field() ?>
                                <button class="btn-danger">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Delete
                                </button>
                            </form>
                        </div>

                        <!-- Quick credential update -->
                        <form method="POST" action="<?= htmlspecialchars((string)(url('/admin/judges/' . $judge->id))) ?>" class="flex items-end gap-2">
                            <?= csrf_field() ?>
                            <div class="flex-1">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars((string)($judge->name)) ?>" class="form-input text-xs py-1.5">
                            </div>
                            <div style="width:120px;">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars((string)($judge->username)) ?>" class="form-input text-xs py-1.5">
                            </div>
                            <div style="width:120px;">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-input text-xs py-1.5" placeholder="Leave blank">
                            </div>
                            <button type="submit" class="btn-teal py-1.5 px-3 flex-shrink-0">Update</button>
                        </form>

                        <?php if(!empty($judge->events)): ?>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <?php foreach($judge->events as $ev): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(245,158,11,0.12);color:#f59e0b;border:1px solid rgba(245,158,11,0.2);">
                                        <?= htmlspecialchars((string)($ev->name)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
    // Clear errors from session after rendering
    unset($_SESSION['errors']);
    unset($_SESSION['old']);
?>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>
