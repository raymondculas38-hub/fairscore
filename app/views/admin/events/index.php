

<?php $__sections['title'] = 'Events'; ?>
<?php $__sections['page-title'] = 'Events'; ?>
<?php $__sections['page-subtitle'] = 'Manage scoring events'; ?>

<?php ob_start(); ?>
    <a href="<?= htmlspecialchars((string)(route('admin.events.create'))) ?>" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Event
    </a>
<?php $__sections['header-actions'] = ob_get_clean(); ?>

<?php ob_start(); ?>

<div class="glass-card animate-fade-in-up">
    <table class="data-table">
        <thead>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Status</th>
                <th>Participants</th>
                <th>Criteria</th>
                <th>Judges</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($events)): $__empty_forelse = true; else: foreach($events as $event): ?>
                <tr class="animate-fade-in-up">
                    <td>
                        <div class="font-semibold" style="color:#e2e8f0;"><?= htmlspecialchars((string)($event->name)) ?></div>
                        <?php if($event->pin): ?>
                            <div class="mt-1 flex items-center gap-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.25);">
                                    PIN: <?= htmlspecialchars((string)($event->pin)) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if($event->description): ?>
                            <div class="text-xs mt-1 truncate max-w-xs" style="color:#475569;"><?= htmlspecialchars((string)($event->description)) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="color:#64748b;">
                        <?= htmlspecialchars((string)($event->event_date ? date('M d, Y', strtotime($event->event_date)) : '—')) ?>
                    </td>
                    <td>
                        <?php if($event->status === 'live'): ?>
                            <span class="badge-live">LIVE</span>
                        <?php elseif($event->status === 'completed'): ?>
                            <span class="badge-completed">Completed</span>
                        <?php else: ?>
                            <span class="badge-upcoming">Upcoming</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= htmlspecialchars((string)(route('admin.participants.index', $event))) ?>" class="btn-teal">
                            <?= htmlspecialchars((string)($event->participants_count)) ?>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </td>
                    <td>
                        <a href="<?= htmlspecialchars((string)(route('admin.criteria.index', $event))) ?>" class="btn-teal">
                            <?= htmlspecialchars((string)($event->criteria_count)) ?>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </td>
                    <td style="color:#94a3b8;"><?= htmlspecialchars((string)($event->judges_count)) ?></td>
                    <td>
                        <div class="flex items-center gap-2 flex-wrap">
                            
                            <form method="POST" action="<?= htmlspecialchars((string)(route('admin.events.toggle', $event))) ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-secondary text-xs py-1.5 px-2.5">
                                    <?php if($event->status === 'upcoming'): ?> ▶ Go Live
                                    <?php elseif($event->status === 'live'): ?> ✓ Complete
                                    <?php else: ?> ↺ Reset <?php endif; ?>
                                </button>
                            </form>

                            
                            <a href="<?= htmlspecialchars((string)(route('admin.events.edit', $event))) ?>" class="btn-teal">Edit</a>

                            
                            <a href="<?= htmlspecialchars((string)(route('leaderboard.show', $event))) ?>" target="_blank" class="btn-secondary text-xs py-1.5 px-2.5" title="Open Scoreboard">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            </a>

                            
                            <form method="POST" action="<?= htmlspecialchars((string)(route('admin.events.destroy', $event))) ?>" onsubmit="return confirm('Delete this event and all its data?')">
                                <?= csrf_field() ?> <input type="hidden" name="_method" value="DELETE">
                                <button class="btn-danger">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; if(isset($__empty_forelse)): unset($__empty_forelse); ?>
                <tr>
                    <td colspan="7" class="text-center py-14" style="color:#334155;">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <p class="text-sm mb-3">No events created yet.</p>
                        <a href="<?= htmlspecialchars((string)(route('admin.events.create'))) ?>" class="btn-primary">Create First Event</a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>