

<?php $__sections['title'] = 'Score Breakdown — ' . $event->name; ?>
<?php $__sections['page-title'] = 'Score Breakdown'; ?>
<?php $__sections['page-subtitle'] = $event->name; ?>

<?php ob_start(); ?>
    <a href="<?= htmlspecialchars((string)(route('admin.events.edit', $event))) ?>" class="btn-secondary">← Back to Event</a>
    <a href="<?= htmlspecialchars((string)(route('leaderboard.show', $event))) ?>" target="_blank" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
        Live Scoreboard
    </a>
<?php $__sections['header-actions'] = ob_get_clean(); ?>

<?php ob_start(); ?>
<?php $eventJudges = $event->judges(); ?>
<style>
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .tab-btn.active { 
        background: rgba(245,158,11,0.15) !important; 
        color: #f59e0b !important; 
        border-color: rgba(245,158,11,0.3) !important;
    }
</style>

<div class="animate-fade-in-up">
    
    <div class="flex flex-wrap gap-2 mb-6" id="tabs-container">
        <button class="tab-btn active px-4 py-2 rounded-lg text-sm font-semibold transition-colors" style="background:rgba(255,255,255,0.04);color:#94a3b8;border:1px solid rgba(255,255,255,0.08);" onclick="switchTab(event, 'overall')">Overall</button>
        <?php foreach($breakdown as $critId => $data): ?>
            <button class="tab-btn px-4 py-2 rounded-lg text-sm font-semibold transition-colors" style="background:rgba(255,255,255,0.04);color:#94a3b8;border:1px solid rgba(255,255,255,0.08);" onclick="switchTab(event, 'crit-<?= htmlspecialchars((string)($critId)) ?>')"><?= htmlspecialchars((string)($data['criterion']->name)) ?></button>
        <?php endforeach; ?>
    </div>

    
    <div id="tab-overall" class="tab-content active glass-card overflow-hidden">
        <div class="px-5 py-4 border-b" style="border-color:rgba(255,255,255,0.06);">
            <h3 class="font-bold text-sm" style="color:#e2e8f0;">Overall Rankings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" style="color:#cbd5e1;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.04);border-bottom:1px solid rgba(255,255,255,0.06);">
                        <th class="px-5 py-3 font-semibold">Rank</th>
                        <th class="px-5 py-3 font-semibold">Contestant</th>
                        <th class="px-5 py-3 font-semibold text-right">Raw Total</th>
                        <th class="px-5 py-3 font-semibold text-right text-amber-400">Weighted Average</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                    <?php foreach($overallLeaderboard as $entry): ?>
                        <tr class="hover:bg-white/5 transition-colors <?= htmlspecialchars((string)($entry->rank == 1 ? 'bg-amber-500/5' : '')) ?>">
                            <td class="px-5 py-3 font-bold">
                                <?php if($entry->rank == 1): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        1st (Overall Winner)
                                    </span>
                                <?php elseif($entry->rank == 2): ?>
                                    <span class="text-slate-300">2nd</span>
                                <?php elseif($entry->rank == 3): ?>
                                    <span class="text-amber-700">3rd</span>
                                <?php else: ?>
                                    <span class="text-slate-500"><?= htmlspecialchars((string)($entry->rank)) ?>th</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-slate-200"><?= htmlspecialchars((string)($entry->name)) ?></span>
                                <?php if($entry->contestant_number): ?>
                                    <span class="text-xs text-slate-500 ml-2">#<?= htmlspecialchars((string)($entry->contestant_number)) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-right font-mono"><?= htmlspecialchars((string)(number_format($entry->total_raw_score, 2))) ?></td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-amber-400"><?= htmlspecialchars((string)(number_format($entry->weighted_avg, 2))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php foreach($breakdown as $critId => $data): ?>
        <div id="tab-crit-<?= htmlspecialchars((string)($critId)) ?>" class="tab-content glass-card overflow-hidden">
            <div class="px-5 py-4 border-b flex justify-between items-center" style="border-color:rgba(255,255,255,0.06);">
                <h3 class="font-bold text-sm" style="color:#e2e8f0;"><?= htmlspecialchars((string)($data['criterion']->name)) ?> Breakdown</h3>
                <span class="text-xs text-slate-500">Weight: <?= htmlspecialchars((string)($data['criterion']->weight)) ?> &times; | Max: <?= htmlspecialchars((string)($data['criterion']->max_score)) ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" style="color:#cbd5e1;">
                    <thead>
                        <tr style="background:rgba(255,255,255,0.04);border-bottom:1px solid rgba(255,255,255,0.06);">
                            <th class="px-5 py-3 font-semibold w-32">Rank</th>
                            <th class="px-5 py-3 font-semibold">Contestant</th>
                            <?php foreach($eventJudges as $judge): ?>
                                <th class="px-5 py-3 font-semibold text-center text-xs" style="color:#94a3b8;"><?= htmlspecialchars((string)($judge->name)) ?></th>
                            <?php endforeach; ?>
                            <th class="px-5 py-3 font-semibold text-right text-teal-400">Total Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                        <?php foreach($data['participants'] as $p): ?>
                            <tr class="hover:bg-white/5 transition-colors <?= htmlspecialchars((string)($p['rank'] == 1 ? 'bg-amber-500/5' : '')) ?>">
                                <td class="px-5 py-3 font-bold">
                                    <?php if($p['rank'] == 1): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                            1st (Best)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-500"><?= htmlspecialchars((string)($p['rank'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-medium text-slate-200"><?= htmlspecialchars((string)($p['participant']->name)) ?></span>
                                </td>
                                <?php foreach($eventJudges as $judge): ?>
                                    <td class="px-5 py-3 text-center font-mono text-xs">
                                        <?php if(isset($p['judgeScores'][$judge->id]) && $p['judgeScores'][$judge->id] > 0): ?>
                                            <?= htmlspecialchars((string)(number_format($p['judgeScores'][$judge->id], 2))) ?>
                                        <?php else: ?>
                                            <span class="text-slate-600">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="px-5 py-3 text-right font-mono font-bold text-teal-400"><?= htmlspecialchars((string)(number_format($p['total'], 2))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<script>
    // Tab switching logic
    function switchTab(e, tabId) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        // Remove active class from buttons
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active');
        });
        
        // Show target content
        document.getElementById('tab-' + tabId).classList.add('active');
        
        // Find clicked button and style it active
        if (e && e.currentTarget) {
            e.currentTarget.classList.add('active');
        }
        
        // Save current tab to localStorage so it persists across refreshes
        localStorage.setItem('activeBreakdownTab_<?= htmlspecialchars((string)($event->id)) ?>', tabId);
    }
    
    // Restore tab on load
    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('activeBreakdownTab_<?= htmlspecialchars((string)($event->id)) ?>');
        if (savedTab && document.getElementById('tab-' + savedTab)) {
            // Find the button that corresponds to the saved tab
            const buttons = document.querySelectorAll('.tab-btn');
            for(let btn of buttons) {
                if (savedTab === 'overall' && btn.innerText.trim() === 'Overall') {
                    switchTab({currentTarget: btn}, 'overall');
                    break;
                } else if (savedTab.startsWith('crit-') && btn.getAttribute('onclick').includes(savedTab)) {
                    switchTab({currentTarget: btn}, savedTab);
                    break;
                }
            }
        }
    });

    // Auto-refresh logic (every 10 seconds to keep live display updated)
    setTimeout(function() {
        window.location.reload();
    }, 10000);
</script>
<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>