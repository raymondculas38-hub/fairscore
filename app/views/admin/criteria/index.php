

<?php $__sections['title'] = 'Criteria — ' . $event->name; ?>
<?php $__sections['page-title'] = 'Scoring Criteria'; ?>
<?php $__sections['page-subtitle'] = $event->name; ?>

<?php ob_start(); ?>
    <a href="<?= htmlspecialchars((string)(route('admin.participants.index', $event))) ?>" class="btn-secondary">← Participants</a>
    <a href="<?= htmlspecialchars((string)(route('admin.events.edit', $event))) ?>" class="btn-teal">Event Settings</a>
<?php $__sections['header-actions'] = ob_get_clean(); ?>

<?php ob_start(); ?>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3 animate-fade-in-up">

    
    <div class="glass-card p-5 h-fit">
        <h3 class="text-sm font-bold mb-4" style="color:#e2e8f0;">Add Criteria</h3>
        <form method="POST" action="<?= htmlspecialchars((string)(route('admin.criteria.store', $event))) ?>">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Criteria Name <span style="color:#f87171;">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars((string)(old('name'))) ?>" class="form-input" placeholder="e.g. Talent, Q&A, Beauty" required>
                    <?php if(isset($_SESSION['errors']['name'])): $message = $_SESSION['errors']['name']; ?><p class="text-xs mt-1" style="color:#f87171;"><?= htmlspecialchars((string)($message)) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Max Score <span style="color:#f87171;">*</span></label>
                    <input type="number" name="max_score" value="<?= htmlspecialchars((string)(old('max_score', 100))) ?>" min="1" max="1000" step="0.5" class="form-input" required>
                    <?php if(isset($_SESSION['errors']['max_score'])): $message = $_SESSION['errors']['max_score']; ?><p class="text-xs mt-1" style="color:#f87171;"><?= htmlspecialchars((string)($message)) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Weight</label>
                    <input type="number" name="weight" value="<?= htmlspecialchars((string)(old('weight', 1.0))) ?>" min="0.01" max="100" step="0.01" class="form-input">
                    <p class="text-xs mt-1" style="color:#475569;">Higher weight = more influence on final score</p>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full justify-center mt-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Criteria
            </button>
        </form>
    </div>

    
    <div class="lg:col-span-2 glass-card">
        <div class="px-5 py-4 flex items-center justify-between border-b" style="border-color:rgba(255,255,255,0.06);">
            <h3 class="text-sm font-bold" style="color:#e2e8f0;">Criteria (<?= htmlspecialchars((string)(count($criteria))) ?>)</h3>
            <?php if(count($criteria) > 0): ?>
                <span class="text-xs" style="color:#475569;">
                    Total weight: <?= htmlspecialchars((string)(number_format(array_reduce($criteria, fn($s, $c) => $s + $c->weight, 0), 2))) ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
            <?php if(empty($criteria)): $__empty_forelse = true; else: foreach($criteria as $c): ?>
                <div class="flex items-center gap-4 px-5 py-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold truncate" style="color:#e2e8f0;"><?= htmlspecialchars((string)($c->name)) ?></span>
                            <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(245,158,11,0.15);color:#f59e0b;">
                                ×<?= htmlspecialchars((string)($c->weight)) ?>
                            </span>
                        </div>
                        <div class="text-xs mt-0.5" style="color:#475569;">Max: <?= htmlspecialchars((string)(number_format($c->max_score, 0))) ?> pts</div>
                    </div>

                    
                    <form method="POST" action="<?= htmlspecialchars((string)(route('admin.criteria.update', [$event, $c]))) ?>" class="flex items-center gap-2">
                        <?= csrf_field() ?> <input type="hidden" name="_method" value="PUT">
                        <input type="text" name="name" value="<?= htmlspecialchars((string)($c->name)) ?>" class="form-input text-xs py-1.5" style="width:110px;">
                        <input type="number" name="max_score" value="<?= htmlspecialchars((string)($c->max_score)) ?>" class="form-input text-xs py-1.5" style="width:65px;" min="1" step="0.5">
                        <input type="number" name="weight" value="<?= htmlspecialchars((string)($c->weight)) ?>" class="form-input text-xs py-1.5" style="width:60px;" min="0.01" step="0.01">
                        <button type="submit" class="btn-teal py-1.5 px-2.5 text-xs">Save</button>
                    </form>

                    <form method="POST" action="<?= htmlspecialchars((string)(route('admin.criteria.destroy', [$event, $c]))) ?>" onsubmit="return confirm('Remove this criteria? All related scores will be deleted.')">
                        <?= csrf_field() ?> <input type="hidden" name="_method" value="DELETE">
                        <button class="btn-danger py-1.5 px-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; endif; if(isset($__empty_forelse)): unset($__empty_forelse); ?>
                <div class="py-12 text-center" style="color:#334155;">
                    <div class="text-4xl mb-3">⭐</div>
                    <p class="text-sm">No criteria yet. Add scoring categories on the left.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>