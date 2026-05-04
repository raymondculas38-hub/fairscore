

<?php $__sections['title'] = 'Scoreboard'; ?>
<?php $__sections['page-title'] = 'Scoreboard'; ?>
<?php $__sections['page-subtitle'] = 'Select an event to view the detailed score breakdown'; ?>

<?php ob_start(); ?>

<div class="animate-fade-in-up">

    <?php if(empty($events)): ?>
        <div class="glass-card p-16 text-center">
            <div class="text-6xl mb-4">🏆</div>
            <h2 class="text-xl font-bold mb-2" style="color:#e2e8f0;">No Events Found</h2>
            <p class="text-sm" style="color:#475569;">Create an event first to view its scoreboard.</p>
        </div>
    <?php else: ?>

        
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach($events as $event): ?>
                <?php
                    $statusColor = match($event->status) {
                        'live'      => ['badge' => 'badge-live',      'border' => 'rgba(34,197,94,0.2)',   'glow' => 'rgba(34,197,94,0.06)'],
                        'completed' => ['badge' => 'badge-completed', 'border' => 'rgba(100,116,139,0.2)', 'glow' => 'rgba(100,116,139,0.04)'],
                        default     => ['badge' => 'badge-upcoming',  'border' => 'rgba(245,158,11,0.15)', 'glow' => 'rgba(245,158,11,0.03)'],
                    };
                    $hasScores = $event->scores_count > 0;
                ?>

                <div class="glass-card overflow-hidden transition-all duration-200 hover:scale-[1.01]"
                     style="border-color:<?= htmlspecialchars((string)($statusColor['border'])) ?>;background:linear-gradient(135deg, <?= htmlspecialchars((string)($statusColor['glow'])) ?>, transparent);">

                    
                    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:rgba(255,255,255,0.06);">
                        <div class="flex-1 min-w-0 mr-3">
                            <h3 class="font-bold text-base truncate" style="color:#f1f5f9;"><?= htmlspecialchars((string)($event->name)) ?></h3>
                            <p class="text-xs mt-0.5" style="color:#475569;">
                                <?= htmlspecialchars((string)($event->event_date ? date('M d, Y', strtotime($event->event_date)) : 'No date set')) ?>
                            </p>
                        </div>
                        <?php if($event->status === 'live'): ?>
                            <span class="badge-live flex-shrink-0">LIVE</span>
                        <?php elseif($event->status === 'completed'): ?>
                            <span class="badge-completed flex-shrink-0">Done</span>
                        <?php else: ?>
                            <span class="badge-upcoming flex-shrink-0">Upcoming</span>
                        <?php endif; ?>
                    </div>

                    
                    <div class="px-5 py-3 grid grid-cols-3 gap-2 border-b" style="border-color:rgba(255,255,255,0.04);">
                        <div class="text-center">
                            <div class="text-lg font-black" style="color:#f59e0b;"><?= htmlspecialchars((string)($event->participants_count)) ?></div>
                            <div class="text-[10px] uppercase tracking-wider" style="color:#475569;">Contestants</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-black" style="color:#818cf8;"><?= htmlspecialchars((string)($event->judges_count)) ?></div>
                            <div class="text-[10px] uppercase tracking-wider" style="color:#475569;">Judges</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-black" style="color:#2dd4bf;"><?= htmlspecialchars((string)($event->criteria_count)) ?></div>
                            <div class="text-[10px] uppercase tracking-wider" style="color:#475569;">Criteria</div>
                        </div>
                    </div>

                    
                    <div class="px-5 py-3">
                        <?php if($hasScores): ?>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-medium" style="color:#4ade80;"><?= htmlspecialchars((string)($event->scores_count)) ?> scores recorded</span>
                            </div>
                        <?php else: ?>
                            <span class="text-xs" style="color:#334155;">No scores yet</span>
                        <?php endif; ?>
                    </div>

                    
                    <div class="px-5 pb-4">
                        <?php if($hasScores): ?>
                            <a href="<?= htmlspecialchars((string)(route('admin.scoreboard.show', $event))) ?>"
                               class="btn-primary w-full justify-center text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                View Score Breakdown
                            </a>
                        <?php else: ?>
                            <button disabled class="btn-secondary w-full justify-center text-sm opacity-40 cursor-not-allowed">
                                Waiting for Scores
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>