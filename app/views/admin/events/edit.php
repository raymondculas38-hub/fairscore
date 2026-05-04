<?php $__sections['title'] = 'Edit Event — ' . htmlspecialchars((string)($event->name)); ?>
<?php $__sections['page-title'] = 'Edit Event'; ?>
<?php $__sections['page-subtitle'] = htmlspecialchars((string)($event->name)); ?>

<?php ob_start(); ?>
    <a href="<?= htmlspecialchars((string)(route('admin.events.index'))) ?>" class="btn-secondary">← Back to Events</a>
<?php $__sections['header-actions'] = ob_get_clean(); ?>

<?php ob_start(); ?>

<div class="max-w-4xl animate-fade-in-up">
    <div class="glass-card p-6">
        <form method="POST" action="<?= htmlspecialchars((string)(route('admin.events.update', $event))) ?>">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-5">
                    <h3 class="text-sm font-bold border-b pb-2" style="color:#e2e8f0;border-color:rgba(255,255,255,0.06);">Event Details</h3>
                    
                    <div>
                        <label class="form-label">Event Name <span style="color:#f87171;">*</span></label>
                        <input type="text" name="name" value="<?= htmlspecialchars((string)(old('name', $event->name))) ?>" class="form-input" required>
                        <?php if(isset($_SESSION['errors']['name'])): $message = $_SESSION['errors']['name']; ?><p class="text-xs mt-1" style="color:#f87171;"><?= htmlspecialchars((string)($message)) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-input"><?= htmlspecialchars((string)(old('description', $event->description))) ?></textarea>
                    </div>

                    <div>
                        <label class="form-label">Event Date</label>
                        <?php 
                            $dateVal = $event->event_date;
                            // Format for datetime-local input
                            if ($dateVal) {
                                $dateVal = date('Y-m-d\TH:i', strtotime($dateVal));
                            }
                        ?>
                        <input type="datetime-local" name="event_date" value="<?= htmlspecialchars((string)(old('event_date', $dateVal))) ?>" class="form-input">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input" style="background-color: #000000; color: #ffffff;">
                                <option value="upcoming" <?= htmlspecialchars((string)(old('status', $event->status)==='upcoming' ? 'selected':'')) ?>>Upcoming</option>
                                <option value="live"     <?= htmlspecialchars((string)(old('status', $event->status)==='live' ? 'selected':'')) ?>>Live</option>
                                <option value="completed"<?= htmlspecialchars((string)(old('status', $event->status)==='completed' ? 'selected':'')) ?>>Completed</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Event PIN Code <span style="color:#f87171;">*</span></label>
                            <input type="text" name="pin" value="<?= htmlspecialchars((string)(old('pin', $event->pin))) ?>" class="form-input font-mono tracking-widest text-center" maxlength="6" required>
                            <?php if(isset($_SESSION['errors']['pin'])): $message = $_SESSION['errors']['pin']; ?><p class="text-xs mt-1" style="color:#f87171;"><?= htmlspecialchars((string)($message)) ?></p><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <h3 class="text-sm font-bold border-b pb-2" style="color:#e2e8f0;border-color:rgba(255,255,255,0.06);">Assign Judges</h3>
                    
                    <div class="glass-card p-4 h-[300px] overflow-y-auto" style="background:rgba(0,0,0,0.2);">
                        <?php if(empty($allJudges)): ?>
                            <p class="text-sm text-slate-400 text-center mt-10">No judges available. <a href="<?= htmlspecialchars((string)(route('admin.judges.index'))) ?>" class="text-amber-500 hover:underline">Create a judge account first.</a></p>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach($allJudges as $judge): ?>
                                    <label class="flex items-center gap-3 p-3 rounded-xl border transition-colors cursor-pointer hover:bg-white/5" style="border-color:rgba(255,255,255,0.05);">
                                        <input type="checkbox" name="judges[]" value="<?= htmlspecialchars((string)($judge->id)) ?>" 
                                            class="w-4 h-4 text-amber-500 bg-slate-800 border-slate-600 rounded focus:ring-amber-500 focus:ring-offset-slate-900"
                                            <?= in_array($judge->id, old('judges', $assignedJudges)) ? 'checked' : '' ?>>
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0" style="background:rgba(99,102,241,0.2);color:#818cf8;">
                                                <?= htmlspecialchars((string)(strtoupper(substr($judge->name, 0, 2)))) ?>
                                            </div>
                                            <span class="text-sm font-medium" style="color:#e2e8f0;"><?= htmlspecialchars((string)($judge->name)) ?></span>
                                            <span class="text-xs" style="color:#475569;">(@<?= htmlspecialchars((string)($judge->username)) ?>)</span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs" style="color:#475569;">Select the judges who will evaluate this event.</p>
                </div>
            </div>

            <div class="flex gap-3 mt-8 pt-6 border-t items-center" style="border-color:rgba(255,255,255,0.06);">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Save Changes
                </button>
                <a href="<?= htmlspecialchars((string)(route('admin.events.index'))) ?>" class="btn-secondary">Cancel</a>
                
                <div class="ml-auto">
                    <button type="submit" form="broadcast-pin-form" class="btn-teal">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                        Broadcast PIN to Judges
                    </button>
                </div>
            </div>
        </form>
        
        <form id="broadcast-pin-form" action="<?= htmlspecialchars((string)(url('admin/events/'.$event->id.'/broadcast-pin'))) ?>" method="POST" class="hidden">
            <?= csrf_field() ?>
        </form>
    </div>
</div>

<?php 
    // Clear errors from session after rendering
    unset($_SESSION['errors']);
    unset($_SESSION['old']);
?>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>
