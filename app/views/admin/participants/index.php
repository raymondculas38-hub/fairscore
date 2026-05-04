

<?php $__sections['title'] = 'Participants — ' . $event->name; ?>
<?php $__sections['page-title'] = 'Participants'; ?>
<?php $__sections['page-subtitle'] = $event->name; ?>

<?php ob_start(); ?>
    <a href="<?= htmlspecialchars((string)(route('admin.events.edit', $event))) ?>" class="btn-secondary">← Event Settings</a>
    <a href="<?= htmlspecialchars((string)(route('admin.criteria.index', $event))) ?>" class="btn-teal">Criteria →</a>
<?php $__sections['header-actions'] = ob_get_clean(); ?>

<?php ob_start(); ?>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3 animate-fade-in-up">

    
    <div class="glass-card p-5 h-fit">
        <h3 class="text-sm font-bold mb-4" style="color:#e2e8f0;">Add Participant</h3>
        <form method="POST" action="<?= htmlspecialchars((string)(route('admin.participants.store', $event))) ?>">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Full Name <span style="color:#f87171;">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars((string)(old('name'))) ?>" class="form-input" placeholder="e.g. Maria Santos" required>
                    <?php if(isset($_SESSION['errors']['name'])): $message = $_SESSION['errors']['name']; ?><p class="text-xs mt-1" style="color:#f87171;"><?= htmlspecialchars((string)($message)) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Contestant #</label>
                    <input type="number" name="contestant_number" value="<?= htmlspecialchars((string)(old('contestant_number', count($participants) + 1))) ?>" min="1" class="form-input" placeholder="Auto-numbered">
                </div>
            </div>
            <button type="submit" class="btn-primary w-full justify-center mt-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Participant
            </button>
        </form>
    </div>

    
    <div class="lg:col-span-2 glass-card">
        <div class="px-5 py-4 flex items-center justify-between border-b" style="border-color:rgba(255,255,255,0.06);">
            <h3 class="text-sm font-bold" style="color:#e2e8f0;">Participants (<?= htmlspecialchars((string)(count($participants))) ?>)</h3>
        </div>
        <div class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
            <?php if(empty($participants)): $__empty_forelse = true; else: foreach($participants as $p): ?>
                <div class="flex items-center gap-4 px-5 py-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0" style="background:rgba(245,158,11,0.15);color:#f59e0b;">
                        #<?= htmlspecialchars((string)($p->contestant_number ?? '?')) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-sm font-semibold" style="color:#e2e8f0;"><?= htmlspecialchars((string)($p->name)) ?></span>
                    </div>

                    
                    <form method="POST" action="<?= htmlspecialchars((string)(route('admin.participants.update', [$event, $p]))) ?>" class="flex items-center gap-2">
                        <?= csrf_field() ?> <input type="hidden" name="_method" value="PUT">
                        <input type="text" name="name" value="<?= htmlspecialchars((string)($p->name)) ?>" class="form-input text-xs py-1.5" style="width:140px;">
                        <input type="number" name="contestant_number" value="<?= htmlspecialchars((string)($p->contestant_number)) ?>" class="form-input text-xs py-1.5" style="width:60px;">
                        <button type="submit" class="btn-teal py-1.5 px-2.5 text-xs">Save</button>
                    </form>

                    <form method="POST" action="<?= htmlspecialchars((string)(route('admin.participants.destroy', [$event, $p]))) ?>" onsubmit="return confirm('Remove this participant?')">
                        <?= csrf_field() ?> <input type="hidden" name="_method" value="DELETE">
                        <button class="btn-danger py-1.5 px-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; endif; if(isset($__empty_forelse)): unset($__empty_forelse); ?>
                <div class="py-12 text-center" style="color:#334155;">
                    <div class="text-4xl mb-3">👤</div>
                    <p class="text-sm">No participants yet. Add one on the left.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>