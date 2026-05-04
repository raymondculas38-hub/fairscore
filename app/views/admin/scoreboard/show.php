<?php $__sections['title'] = 'Scoreboard Manager — ' . $event->name; ?>
<?php $__sections['page-title'] = 'Scoreboard Manager'; ?>
<?php $__sections['page-subtitle'] = $event->name; ?>

<?php ob_start(); ?>
    <a href="<?= htmlspecialchars((string)(route('admin.scoreboard.index'))) ?>" class="btn-secondary">← Back to Scoreboards</a>
    <a href="<?= htmlspecialchars((string)(url('live/'.$event->id))) ?>" target="_blank" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
        Open Live View
    </a>
<?php $__sections['header-actions'] = ob_get_clean(); ?>

<?php ob_start(); ?>

<div class="animate-fade-in-up">
    
    <!-- Display Settings -->
    <div class="glass-card mb-6 overflow-hidden" style="border-color:rgba(34,197,94,0.3); box-shadow: 0 4px 20px -2px rgba(34,197,94,0.1);">
        <div class="px-5 py-4 border-b flex items-center gap-3" style="border-color:rgba(255,255,255,0.06); background: rgba(34,197,94,0.05);">
            <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center text-green-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            </div>
            <div>
                <h3 class="font-bold text-base text-green-400">Public Display Settings</h3>
                <p class="text-xs text-slate-400">Control what the audience sees on the live scoreboard URL.</p>
            </div>
        </div>
        
        <form action="<?= htmlspecialchars((string)(route('admin.scoreboard.setDisplay', $event))) ?>" method="POST" class="p-5 flex flex-wrap md:flex-nowrap items-end gap-4">
            <?= csrf_field() ?>
            
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Display Mode</label>
                <select name="mode" id="display-mode" class="form-input w-full" style="background-color: #0f172a; color: #f8fafc; border: 1px solid rgba(255,255,255,0.1);" onchange="toggleCriteriaSelect()">
                    <option value="overall" <?= htmlspecialchars((string)(($event->public_display_mode ?? 'overall') === 'overall' ? 'selected' : '')) ?>>Overall Rankings</option>
                    <option value="criteria" <?= htmlspecialchars((string)(($event->public_display_mode ?? 'overall') === 'criteria' ? 'selected' : '')) ?>>Specific Category</option>
                </select>
            </div>
            
            <div class="flex-1 min-w-[200px] <?= htmlspecialchars((string)(($event->public_display_mode ?? 'overall') === 'criteria' ? '' : 'hidden')) ?>" id="criteria-select-container">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Select Category</label>
                <select name="criteria_id" class="form-input w-full" style="background-color: #0f172a; color: #f8fafc; border: 1px solid rgba(255,255,255,0.1);">
                    <?php foreach($breakdown as $critId => $data): ?>
                        <option value="<?= htmlspecialchars((string)($critId)) ?>" <?= htmlspecialchars((string)(($event->public_criteria_id ?? null) == $critId ? 'selected' : '')) ?>><?= htmlspecialchars((string)($data['criterion']->name)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex-shrink-0 w-full md:w-auto">
                <button type="submit" class="btn-primary w-full py-2.5">Update Display</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Overall Leaderboard -->
        <div class="glass-card xl:col-span-2 overflow-hidden">
            <div class="px-5 py-4 border-b flex justify-between items-center" style="border-color:rgba(255,255,255,0.06);">
                <h3 class="font-bold text-sm" style="color:#e2e8f0;">Overall Leaderboard</h3>
                <span class="badge-live">LIVE</span>
            </div>
            <div class="overflow-x-auto max-h-[600px]">
                <table class="w-full text-left text-sm" style="color:#cbd5e1;">
                    <thead class="sticky top-0 z-10" style="background:#0a1628;box-shadow:0 1px 0 rgba(255,255,255,0.06);">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Rank</th>
                            <th class="px-5 py-3 font-semibold">Contestant</th>
                            <th class="px-5 py-3 font-semibold text-right">Raw Score</th>
                            <th class="px-5 py-3 font-semibold text-right text-amber-400">Weighted Average</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                        <?php if(empty($overallLeaderboard)): ?>
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-500">No contestants found.</td>
                            </tr>
                        <?php else: foreach($overallLeaderboard as $entry): ?>
                            <tr class="hover:bg-white/5 transition-colors <?= htmlspecialchars((string)($entry->rank == 1 ? 'bg-amber-500/5' : '')) ?>">
                                <td class="px-5 py-3 font-bold">
                                    <?php if($entry->rank == 1 && $entry->weighted_avg > 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                            1st (Winner)
                                        </span>
                                    <?php elseif($entry->rank == 2 && $entry->weighted_avg > 0): ?>
                                        <span class="text-slate-300">2nd</span>
                                    <?php elseif($entry->rank == 3 && $entry->weighted_avg > 0): ?>
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
                                <td class="px-5 py-3 text-right font-mono font-bold text-amber-400 text-lg"><?= htmlspecialchars((string)(number_format($entry->weighted_avg, 2))) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Category Winners -->
        <div class="glass-card overflow-hidden h-fit">
            <div class="px-5 py-4 border-b" style="border-color:rgba(255,255,255,0.06);">
                <h3 class="font-bold text-sm" style="color:#e2e8f0;">Category Leaders</h3>
            </div>
            <div class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                <?php if(empty($categoryWinners)): ?>
                    <div class="px-5 py-8 text-center text-slate-500 text-sm">No scores recorded yet.</div>
                <?php else: foreach($categoryWinners as $critId => $winData): ?>
                    <div class="px-5 py-4">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2"><?= htmlspecialchars((string)($winData['criterion']->name)) ?></div>
                        
                        <?php if($winData['total'] > 0): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-xs">
                                        #<?= htmlspecialchars((string)($winData['winner']->contestant_number ?? '-')) ?>
                                    </div>
                                    <span class="font-bold text-slate-200"><?= htmlspecialchars((string)($winData['winner']->name)) ?></span>
                                </div>
                                <div class="text-right font-mono font-bold text-teal-400">
                                    <?= htmlspecialchars((string)(number_format($winData['total'], 2))) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-slate-500 italic">No scores yet.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            
            <div class="px-5 py-4 border-t" style="border-color:rgba(255,255,255,0.06); background:rgba(0,0,0,0.2);">
                <a href="<?= htmlspecialchars((string)(url('admin/events/'.$event->id.'/breakdown'))) ?>" class="btn-secondary w-full justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    Full Detail Breakdown
                </a>
            </div>
        </div>

    </div>

</div>

<script>
function toggleCriteriaSelect() {
    const mode = document.getElementById('display-mode').value;
    const container = document.getElementById('criteria-select-container');
    if (mode === 'criteria') {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}
</script>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>
