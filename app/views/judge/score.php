

<?php $__sections['title'] = 'Scoring — ' . $event->name; ?>
<?php $__sections['container-class'] = 'w-full max-w-7xl mx-auto'; ?>

<?php ob_start(); ?>


<div class="mb-4 flex items-center justify-between gap-4 flex-wrap">
    <div class="flex items-center gap-3">
        <a href="<?= htmlspecialchars((string)(route('judge.dashboard'))) ?>" class="btn-secondary text-xs py-1.5 px-3">← Events</a>
        <h2 class="font-black text-xl truncate" style="color:#f1f5f9;"><?= htmlspecialchars((string)($event->name)) ?></h2>
        <span class="badge-live">LIVE</span>
    </div>
    
    
    <?php
        $totalScores = count($criteria) * count($participants);
        $filledScores = count($existingScores);
        $progress = $totalScores > 0 ? round(($filledScores / $totalScores) * 100) : 0;
    ?>
    <div class="w-full sm:w-64 glass-card px-3 py-2 flex-shrink-0">
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-xs font-semibold uppercase tracking-widest" style="color:#94a3b8;">Progress</span>
            <span class="text-xs font-bold" id="progress-text" style="color:#f59e0b;"><?= htmlspecialchars((string)($filledScores)) ?>/<?= htmlspecialchars((string)($totalScores)) ?> (<?= htmlspecialchars((string)($progress)) ?>%)</span>
        </div>
        <div class="w-full h-1.5 rounded-full" style="background:rgba(255,255,255,0.08);">
            <div id="progress-bar" class="h-1.5 rounded-full transition-all duration-500"
                 style="width:<?= htmlspecialchars((string)($progress)) ?>%;background:linear-gradient(90deg,#f59e0b,#2dd4bf);"></div>
        </div>
    </div>
</div>


<div id="save-toast" class="fixed bottom-6 right-4 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all duration-300 opacity-0 pointer-events-none z-50 transform translate-y-4 shadow-lg shadow-black/50"
     style="background:rgba(34,197,94,0.9);color:#fff;backdrop-filter:blur(8px);">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
    Saved automatically
</div>

<div id="error-toast" class="fixed bottom-6 right-4 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all duration-300 opacity-0 pointer-events-none z-50 transform translate-y-4 shadow-lg shadow-black/50"
     style="background:rgba(239,68,68,0.9);color:#fff;backdrop-filter:blur(8px);">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <span id="error-msg">Invalid score limit</span>
</div>


<div class="glass-card animate-fade-in-up" style="border: 1px solid rgba(255,255,255,0.08);">
    <div class="overflow-x-auto rounded-xl">
        <table class="data-table w-full">
            <thead class="sticky top-0 z-10" style="background:#0a1628;box-shadow:0 1px 0 rgba(255,255,255,0.08);">
                <tr>
                    <th class="w-16 text-center whitespace-nowrap">Con #</th>
                    <th class="whitespace-nowrap min-w-[150px]">Contestant Name</th>
                    <?php foreach($criteria as $c): ?>
                        <th class="text-right whitespace-nowrap" style="min-w: 110px;">
                            <div class="font-bold" style="color:#cbd5e1;"><?= htmlspecialchars((string)($c->name)) ?></div>
                            <div class="text-[0.65rem]" style="color:#64748b;">Max: <?= htmlspecialchars((string)(number_format($c->max_score, 0))) ?></div>
                        </th>
                    <?php endforeach; ?>
                    <th class="text-right w-24">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                <?php if(empty($participants)): $__empty_forelse = true; else: foreach($participants as $participant): ?>
                    <tr class="hover:bg-white/5 transition-colors group">
                        
                        <td class="text-center font-black" style="color:#f59e0b;">
                            <?= htmlspecialchars((string)($participant->contestant_number ?? '-')) ?>
                        </td>
                        
                        
                        <td class="font-semibold whitespace-nowrap" style="color:#f1f5f9;">
                            <?= htmlspecialchars((string)($participant->name)) ?>
                        </td>

                        
                        <?php $rowTotal = 0; ?>
                        <?php foreach($criteria as $c): ?>
                            <?php
                                $key = $participant->id . '_' . $c->id;
                                $existing = $existingScores[$key] ?? null;
                                $currentVal = $existing ? (float)$existing->score : '';
                                if ($existing) $rowTotal += $currentVal;
                            ?>
                            <td class="text-right">
                                <div class="relative inline-block w-24 group-hover:-translate-y-0.5 transition-transform">
                                    <input type="number" 
                                           class="form-input score-input text-right font-bold w-full <?= htmlspecialchars((string)($existing ? 'ring-1 ring-green-500/30' : '')) ?>" 
                                           style="padding-right: 0.5rem;"
                                           min="0" 
                                           max="<?= htmlspecialchars((string)($c->max_score)) ?>" 
                                           step="0.5"
                                           value="<?= htmlspecialchars((string)($currentVal)) ?>"
                                           placeholder="-"
                                           data-event="<?= htmlspecialchars((string)($event->id)) ?>"
                                           data-participant="<?= htmlspecialchars((string)($participant->id)) ?>"
                                           data-criteria="<?= htmlspecialchars((string)($c->id)) ?>"
                                           data-max="<?= htmlspecialchars((string)($c->max_score)) ?>"
                                           oninput="validateAndSave(this)">
                                </div>
                            </td>
                        <?php endforeach; ?>

                        
                        <td class="text-right">
                            <span id="total-<?= htmlspecialchars((string)($participant->id)) ?>" class="text-lg font-black" style="color:#f59e0b;">
                                <?= htmlspecialchars((string)(number_format($rowTotal, 1))) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; endif; if(isset($__empty_forelse)): unset($__empty_forelse); ?>
                    <tr>
                        <td colspan="<?= htmlspecialchars((string)(count($criteria) + 3)) ?>" class="text-center py-12" style="color:#64748b;">
                            No contestants have been added to this event yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const totalExpectedScores = <?= htmlspecialchars((string)($totalScores)) ?>;
let currentFilledScores = <?= htmlspecialchars((string)($filledScores)) ?>;
const debounceMap = {};

// Cache existing filled states to track progress changes accurately
const filledKeys = new Set(
    <?= json_encode(array_keys($existingScores)) ?>
);

function validateAndSave(input) {
    const val = parseFloat(input.value);
    const max = parseFloat(input.dataset.max);
    const pId = input.dataset.participant;
    const cId = input.dataset.criteria;
    const eId = input.dataset.event;
    const key = `${pId}_${cId}`;

    // Reset styles
    input.classList.remove('ring-1', 'ring-red-500/60', 'ring-green-500/60', 'ring-green-500/30');
    
    // Check if empty
    if (input.value === '') {
        recalcRowTotal(pId);
        return; // Do not save empty strings
    }

    // Min/Max Validation
    if (isNaN(val) || val < 0 || val > max) {
        input.classList.add('ring-1', 'ring-red-500/60');
        showErrorToast(`Score must be between 0 and ${max}`);
        return;
    }

    // Immediately recalculate local visual UI before network completes
    recalcRowTotal(pId);
    
    // Debounce the network request
    clearTimeout(debounceMap[key]);
    debounceMap[key] = setTimeout(() => {
        commitScore(eId, pId, cId, val, input, key);
    }, 600); // 600ms debounce
}

async function commitScore(eId, pId, cId, score, inputElement, key) {
    try {
        const res = await fetch(`/judge/event/${eId}/score`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                participant_id: pId,
                criteria_id: cId,
                score: score,
            }),
        });

        const data = await res.json();
        if (data.success) {
            // Success Feedback
            inputElement.classList.add('ring-1', 'ring-green-500/60', 'bg-green-500/10');
            setTimeout(() => {
                inputElement.classList.remove('ring-green-500/60', 'bg-green-500/10');
                inputElement.classList.add('ring-1', 'ring-green-500/30'); // Ambient green ring to show it's saved
            }, 500);

            showSuccessToast();
            updateProgress(key);
        } else {
            throw new Error('Server returned failure');
        }
    } catch(e) {
        console.error('Score save failed:', e);
        inputElement.classList.add('ring-1', 'ring-red-500/60');
        showErrorToast('Failed to save. Check connection.');
    }
}

function recalcRowTotal(pId) {
    const inputs = document.querySelectorAll(`input[data-participant="${pId}"]`);
    let total = 0;
    inputs.forEach(inp => {
        const val = parseFloat(inp.value);
        if (!isNaN(val)) total += val;
    });
    document.getElementById(`total-${pId}`).textContent = total.toFixed(1);
}

function updateProgress(key) {
    if (!filledKeys.has(key)) {
        filledKeys.add(key);
        currentFilledScores++;
        
        let pct = 0;
        if (totalExpectedScores > 0) {
            pct = Math.round((currentFilledScores / totalExpectedScores) * 100);
        }
        
        document.getElementById('progress-text').textContent = `${currentFilledScores}/${totalExpectedScores} (${pct}%)`;
        document.getElementById('progress-bar').style.width = `${pct}%`;
    }
}

let successToastTimer;
function showSuccessToast() {
    const toast = document.getElementById('save-toast');
    toast.classList.remove('opacity-0', 'translate-y-4');
    toast.classList.add('opacity-100', 'translate-y-0');
    
    clearTimeout(successToastTimer);
    successToastTimer = setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-4');
    }, 2000);
}

let errorToastTimer;
function showErrorToast(msg) {
    const toast = document.getElementById('error-toast');
    document.getElementById('error-msg').textContent = msg;
    
    toast.classList.remove('opacity-0', 'translate-y-4');
    toast.classList.add('opacity-100', 'translate-y-0');
    
    clearTimeout(errorToastTimer);
    errorToastTimer = setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-4');
    }, 3000);
}
</script>

<?php $__sections['content'] = ob_get_clean(); ?>
<?php require BASE_PATH . '/app/views/layouts/judge.php'; ?>