<?php $__sections['title'] = 'Dashboard'; ?>
<?php $__sections['page-title'] = 'Dashboard'; ?>
<?php $__sections['page-subtitle'] = 'Welcome back, ' . htmlspecialchars(auth()->name ?? ''); ?>

<?php ob_start(); ?>
    <a href="<?= htmlspecialchars((string)(route('admin.events.create'))) ?>" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Event
    </a>
<?php $__sections['header-actions'] = ob_get_clean(); ?>

<?php ob_start(); ?>

<?php
$cards = [
    ['label'=>'Total Events',  'value'=>$stats['total_events'],  'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,0.1)'],
    ['label'=>'Live Events',   'value'=>$stats['live_events'],   'icon'=>'M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z', 'color'=>'#4ade80', 'bg'=>'rgba(34,197,94,0.1)'],
    ['label'=>'Total Judges',  'value'=>$stats['total_judges'],  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'#818cf8', 'bg'=>'rgba(99,102,241,0.1)'],
    ['label'=>'Scores Cast',   'value'=>$stats['total_scores'],  'icon'=>'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.381-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color'=>'#2dd4bf', 'bg'=>'rgba(20,184,166,0.1)'],
];
?>

<div class="grid grid-cols-2 gap-4 mb-6 lg:grid-cols-4">
    <?php foreach($cards as $i => $card): ?>
        <div class="stat-card animate-fade-in-up delay-<?= htmlspecialchars((string)($i+1)) ?>">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-widest" style="color:#475569;"><?= htmlspecialchars((string)($card['label'])) ?></span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:<?= htmlspecialchars((string)($card['bg'])) ?>;">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="<?= htmlspecialchars((string)($card['color'])) ?>" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?= htmlspecialchars((string)($card['icon'])) ?>" /></svg>
                </div>
            </div>
            <div class="text-3xl font-black" style="color:<?= htmlspecialchars((string)($card['color'])) ?>;"><?= htmlspecialchars((string)($card['value'])) ?></div>
        </div>
    <?php endforeach; ?>
</div>


<div class="grid grid-cols-1 gap-5 lg:grid-cols-5">

    <div class="glass-card lg:col-span-3 animate-fade-in-up delay-3">
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:rgba(255,255,255,0.06);">
            <h3 class="font-bold text-sm" style="color:#e2e8f0;">Active Events</h3>
            <a href="<?= htmlspecialchars((string)(route('admin.events.index'))) ?>" class="text-xs font-semibold" style="color:#f59e0b;">View all →</a>
        </div>
        <div class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
            <?php if(empty($activeEvents)): ?>
                <div class="px-5 py-10 text-center" style="color:#334155;">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <p class="text-sm">No events yet.</p>
                    <a href="<?= htmlspecialchars((string)(route('admin.events.create'))) ?>" class="btn-primary inline-flex mt-3">Create your first event</a>
                </div>
            <?php else: foreach($activeEvents as $event): ?>
                <div class="flex items-center gap-4 px-5 py-3.5">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate" style="color:#e2e8f0;"><?= htmlspecialchars((string)($event->name)) ?></p>
                        <p class="text-xs mt-0.5" style="color:#475569;">
                            <?= htmlspecialchars((string)($event->event_date ? date('M d, Y', strtotime($event->event_date)) : 'No date set')) ?>
                        </p>
                    </div>
                    <div>
                        <?php if($event->status === 'live'): ?>
                            <span class="badge-live">LIVE</span>
                        <?php elseif($event->status === 'completed'): ?>
                            <span class="badge-completed">Done</span>
                        <?php else: ?>
                            <span class="badge-upcoming">Upcoming</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= htmlspecialchars((string)(route('admin.events.edit', $event))) ?>" class="btn-teal">Manage</a>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="glass-card lg:col-span-2 animate-fade-in-up delay-4">
        <div class="px-5 py-4 border-b" style="border-color:rgba(255,255,255,0.06);">
            <h3 class="font-bold text-sm" style="color:#e2e8f0;">Live Scoreboards</h3>
        </div>
        <div class="px-5 py-4 space-y-3">
            <?php if(empty($liveEvents)): ?>
                <div class="py-8 text-center" style="color:#334155;">
                    <div class="text-3xl mb-2">🏁</div>
                    <p class="text-sm">No live events.</p>
                    <p class="text-xs mt-1">Set an event to LIVE to see its scoreboard here.</p>
                </div>
            <?php else: foreach($liveEvents as $event): ?>
                <div class="p-3 rounded-xl" style="background:rgba(34,197,94,0.06);border:1px solid rgba(34,197,94,0.15);">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold" style="color:#e2e8f0;"><?= htmlspecialchars((string)($event->name)) ?></p>
                        <span class="badge-live">LIVE</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs" style="color:#475569;">
                        <span><?= htmlspecialchars((string)($event->participants_count ?? 0)) ?> participants</span>
                        <span>·</span>
                        <span><?= htmlspecialchars((string)($event->judges_count ?? 0)) ?> judges</span>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <a href="<?= htmlspecialchars((string)(url('live/'.$event->id))) ?>" target="_blank"
                           class="btn-primary flex-1 justify-center text-xs py-2 px-2 text-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            Scoreboard
                        </a>
                        <a href="<?= htmlspecialchars((string)(url('admin/events/'.$event->id.'/breakdown'))) ?>"
                           class="btn-secondary flex-1 justify-center text-xs py-2 px-2 text-center" style="background:rgba(255,255,255,0.1);">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Breakdown
                        </a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>


<?php if(!empty($liveEvents)): ?>
<?php $firstLiveEvent = reset($liveEvents); ?>
<div class="mt-6 animate-fade-in-up delay-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold" style="color:#f1f5f9;">Judge Status: <?= htmlspecialchars((string)($firstLiveEvent->name)) ?></h2>
        <a href="<?= htmlspecialchars((string)(url('live/'.$firstLiveEvent->id))) ?>" target="_blank" class="text-sm font-semibold hover:underline" style="color:#2dd4bf;">Open Scoreboard →</a>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" style="color:#cbd5e1;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.04);border-bottom:1px solid rgba(255,255,255,0.06);">
                        <th class="px-5 py-3 font-semibold">Judge Name</th>
                        <th class="px-5 py-3 font-semibold text-center">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                    <?php
                    $eventJudges = $firstLiveEvent->judges();
                    $eventScores = $firstLiveEvent->scores();
                    if(empty($eventJudges)): ?>
                        <tr>
                            <td colspan="3" class="px-5 py-6 text-center text-slate-400">
                                No judges assigned to this event.
                            </td>
                        </tr>
                    <?php else: foreach($eventJudges as $judge):
                        $hasSubmitted = (bool) array_filter($eventScores, fn($s) => $s->judge_id == $judge->id);
                    ?>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-5 py-3 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs" style="background:rgba(245,158,11,0.2);color:#f59e0b;">
                                    <?= htmlspecialchars((string)(strtoupper(substr($judge->name, 0, 2)))) ?>
                                </div>
                                <span class="font-medium text-slate-200"><?= htmlspecialchars((string)($judge->name)) ?></span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <?php if($hasSubmitted): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-green-500/20 text-green-400 border border-green-500/30">Submitted</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-amber-500/20 text-amber-400 border border-amber-500/30">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <?php if(!$hasSubmitted): ?>
                                    <form action="<?= htmlspecialchars((string)(url('admin/events/'.$firstLiveEvent->id.'/remind/'.$judge->id))) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-primary text-xs py-1.5 px-3">Remind Judge</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500 italic">No action needed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/admin.php'; ?>