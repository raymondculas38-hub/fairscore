

<?php $__sections['title'] = 'Settings'; ?>
<?php $__sections['page-title'] = 'System Settings'; ?>
<?php $__sections['page-subtitle'] = 'Configure global event parameters and branding'; ?>

<?php ob_start(); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    
    <div class="lg:col-span-2 space-y-6">
        <form action="<?= htmlspecialchars((string)(route('admin.settings.update'))) ?>" method="POST" class="bg-[#0b1426] border border-[#1e293b] rounded-xl overflow-hidden">
            <?= csrf_field() ?>
            
            <div class="p-6 border-b border-[#1e293b]">
                <h2 class="text-lg font-bold text-slate-200">Branding & General</h2>
                <p class="text-sm text-slate-400 mt-1">Customize how the event looks and is named across the system.</p>
                
                <div class="mt-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Event Title</label>
                        <input type="text" name="settings[event_title]" value="<?= htmlspecialchars((string)($settings['event_title']->value ?? 'FairScore Event')) ?>"
                               class="w-full bg-[#050c1a] border border-[#1e293b] rounded-lg px-4 py-2.5 text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Primary Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="settings[theme_primary_color]" value="<?= htmlspecialchars((string)($settings['theme_primary_color']->value ?? '#f59e0b')) ?>"
                                       class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0">
                                <span class="text-xs text-slate-400 font-mono uppercase"><?= htmlspecialchars((string)($settings['theme_primary_color']->value ?? '#f59e0b')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 border-b border-[#1e293b]">
                <h2 class="text-lg font-bold text-slate-200">Scoring Configurations</h2>
                <p class="text-sm text-slate-400 mt-1">Manage rules around how judges interact with participants.</p>
                
                <div class="mt-6 space-y-4">
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-[#1e293b] bg-[#050c1a] cursor-pointer hover:border-slate-700 transition">
                        <input type="hidden" name="settings[allow_score_editing]" value="0">
                        <input type="checkbox" name="settings[allow_score_editing]" value="1" 
                            <?= htmlspecialchars((string)(($settings['allow_score_editing']->value ?? '1') == '1' ? 'checked' : '')) ?>
                            class="w-5 h-5 rounded border-slate-700 bg-slate-800 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-900">
                        <div>
                            <div class="font-medium text-slate-200 text-sm">Allow Judges to Edit Scores</div>
                            <div class="text-xs text-slate-400">If enabled, judges can change their scores until the event is locked.</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-lg border border-[#1e293b] bg-[#050c1a] cursor-pointer hover:border-slate-700 transition">
                        <input type="hidden" name="settings[require_judge_comments]" value="0">
                        <input type="checkbox" name="settings[require_judge_comments]" value="1" 
                            <?= htmlspecialchars((string)(($settings['require_judge_comments']->value ?? '0') == '1' ? 'checked' : '')) ?>
                            class="w-5 h-5 rounded border-slate-700 bg-slate-800 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-900">
                        <div>
                            <div class="font-medium text-slate-200 text-sm">Require Judge Comments</div>
                            <div class="text-xs text-slate-400">Judges must leave feedback for every score they submit.</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="p-6 bg-[#07101f] border-t border-[#1e293b] flex justify-end">
                <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white font-semibold py-2 px-6 rounded-lg transition-colors shadow-lg shadow-amber-900/20">
                    Save General Settings
                </button>
            </div>
        </form>
    </div>

    
    <div class="space-y-6">
        <div class="bg-[#1a0f14] border border-red-900/50 rounded-xl overflow-hidden">
            <div class="p-6 border-b border-red-900/50">
                <div class="flex items-center gap-2 text-red-500 mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <h2 class="text-lg font-bold">Danger Zone</h2>
                </div>
                <p class="text-sm text-red-400/80">These actions are irreversible and will permanently delete data.</p>
                
                <div class="mt-6 pt-4 border-t border-red-900/50">
                    <h3 class="font-semibold text-red-100 text-sm mb-1">Factory Reset Scores</h3>
                    <p class="text-xs text-red-400/80 mb-4">Wipe all submitted scores across the entire system. Useful for clearing practice runs before the real event begins.</p>
                    
                    <form action="<?= htmlspecialchars((string)(route('admin.settings.factory_reset'))) ?>" method="POST" onsubmit="return confirm('Are you absolutely sure? This will delete ALL scores permanently.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full bg-red-600/20 hover:bg-red-600/40 text-red-500 border border-red-900/50 font-medium py-2 px-4 rounded-lg transition-colors text-sm text-center">
                            Wipe All Scores
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>