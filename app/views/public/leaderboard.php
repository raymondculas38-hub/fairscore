<?php
// Public leaderboard — no auth required
$refreshInterval = 30; // seconds
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?= $refreshInterval ?>">
    <title><?= htmlspecialchars((string)($event->name)) ?> — Live Scoreboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <?= vite(['resources/css/app.css', 'resources/js/app.js']) ?>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: radial-gradient(ellipse at top, #0d1e3a 0%, #050c1a 70%);
            min-height: 100vh;
            color: #e2e8f0;
        }

        /* ─── Header ─── */
        .lb-header {
            background: rgba(7,16,31,0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .lb-logo {
            width: 2.5rem; height: 2.5rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 0.85rem; color: #050c1a;
            box-shadow: 0 4px 18px rgba(245,158,11,0.3);
            flex-shrink: 0;
        }
        .lb-event-name { font-size: 1.25rem; font-weight: 800; color: #f1f5f9; }
        .lb-event-sub  { font-size: 0.75rem; color: #475569; letter-spacing: 0.05em; margin-top: 0.1rem; }
        .live-pill {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.3);
            color: #4ade80;
            border-radius: 9999px;
            padding: 0.3rem 0.75rem;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
        }
        .live-dot { width: 0.5rem; height: 0.5rem; border-radius: 50%; background: #4ade80; animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.3;} }

        /* ─── Layout ─── */
        .lb-container { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

        /* ─── Podium ─── */
        .podium-section { display: flex; align-items: flex-end; justify-content: center; gap: 1rem; margin: 2.5rem 0 3rem; }
        .podium-card {
            display: flex; flex-direction: column; align-items: center;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1.25rem;
            padding: 1.5rem 1.25rem 1rem;
            transition: transform 0.3s;
            position: relative; overflow: hidden;
        }
        .podium-card:hover { transform: translateY(-4px); }
        .podium-1 {
            background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(245,158,11,0.03));
            border-color: rgba(245,158,11,0.3);
            box-shadow: 0 8px 40px rgba(245,158,11,0.15);
            padding-top: 2rem;
            min-width: 240px;
        }
        .podium-2, .podium-3 { min-width: 180px; }
        .podium-2 { background: linear-gradient(135deg, rgba(148,163,184,0.1), rgba(148,163,184,0.02)); border-color: rgba(148,163,184,0.2); }
        .podium-3 { background: linear-gradient(135deg, rgba(180,130,80,0.1), rgba(180,130,80,0.02)); border-color: rgba(180,130,80,0.2); }

        .podium-medal { font-size: 2rem; margin-bottom: 0.5rem; }
        .podium-rank { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.6; margin-bottom: 0.35rem; }
        .podium-name { font-size: 1rem; font-weight: 800; text-align: center; color: #f1f5f9; line-height: 1.25; }
        .podium-num  { font-size: 0.7rem; color: #64748b; margin-top: 0.25rem; }
        .podium-score {
            margin-top: 0.75rem;
            padding: 0.35rem 0.9rem;
            border-radius: 9999px;
            font-size: 1.1rem; font-weight: 900; font-variant-numeric: tabular-nums;
        }
        .podium-1 .podium-score { background: rgba(245,158,11,0.2); color: #fbbf24; }
        .podium-2 .podium-score { background: rgba(148,163,184,0.15); color: #cbd5e1; }
        .podium-3 .podium-score { background: rgba(180,130,80,0.15); color: #d4a76a; }
        .crown { position: absolute; top: -2px; left: 50%; transform: translateX(-50%); font-size: 1.5rem; }

        /* ─── Leaderboard Table ─── */
        .lb-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 1.25rem;
            overflow: hidden;
        }
        .lb-card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .lb-card-title { font-size: 0.875rem; font-weight: 700; color: #e2e8f0; }
        .lb-table { width: 100%; border-collapse: collapse; }
        .lb-table th { padding: 0.75rem 1.5rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #475569; text-align: left; background: rgba(255,255,255,0.02); }
        .lb-table th.right { text-align: right; }
        .lb-table td { padding: 0.875rem 1.5rem; font-size: 0.875rem; border-top: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        .lb-table tr:hover td { background: rgba(255,255,255,0.025); }
        .lb-table tr.winner td { background: rgba(245,158,11,0.04); }
        .rank-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 2rem; height: 2rem; border-radius: 50%;
            font-size: 0.75rem; font-weight: 700;
        }
        .rank-1 { background: rgba(245,158,11,0.2); color: #fbbf24; }
        .rank-2 { background: rgba(148,163,184,0.15); color: #cbd5e1; }
        .rank-3 { background: rgba(180,130,80,0.15); color: #d4a76a; }
        .rank-n { background: rgba(255,255,255,0.04); color: #475569; }
        .score-value { font-size: 1rem; font-weight: 800; color: #f59e0b; font-variant-numeric: tabular-nums; text-align: right; }
        .contestant-name { font-weight: 600; color: #e2e8f0; }
        .contestant-num  { font-size: 0.7rem; color: #475569; margin-top: 0.1rem; }

        /* ─── Empty / No Scores ─── */
        .empty-state { text-align: center; padding: 4rem 2rem; color: #334155; }
        .empty-icon  { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.5; }

        /* ─── Footer ─── */
        .lb-footer { text-align: center; margin-top: 2.5rem; font-size: 0.7rem; color: #1e293b; }
        .lb-footer span { color: #334155; }

        @media (max-width: 600px) {
            .podium-section { flex-direction: column; align-items: center; }
            .podium-1, .podium-2, .podium-3 { min-width: 200px; width: 100%; max-width: 280px; }
            .lb-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
        }
    </style>
</head>
<body>

<header class="lb-header">
    <div style="display:flex;align-items:center;gap:0.85rem;">
        <div class="lb-logo">FS</div>
        <div>
            <div class="lb-event-name"><?= htmlspecialchars((string)($event->name)) ?></div>
            <div class="lb-event-sub">
                <?php if($event->event_date): ?>
                    <?= htmlspecialchars((string)(date('F d, Y', strtotime($event->event_date)))) ?> &nbsp;·&nbsp;
                <?php endif; ?>
                Live Scoreboard
            </div>
        </div>
    </div>
    <?php if($event->status === 'live'): ?>
        <div class="live-pill">
            <span class="live-dot"></span>
            Live
        </div>
    <?php elseif($event->status === 'completed'): ?>
        <div style="background:rgba(100,116,139,0.1);border:1px solid rgba(100,116,139,0.25);color:#94a3b8;border-radius:9999px;padding:0.3rem 0.75rem;font-size:0.7rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;">Final Results</div>
    <?php endif; ?>
</header>

<div class="lb-container">

<?php if($displayMode === 'overall'): ?>

    <?php if(empty($leaderboard)): ?>
        <div class="empty-state">
            <div class="empty-icon">🏆</div>
            <p style="font-size:1.1rem;font-weight:700;margin-bottom:0.5rem;color:#475569;">Scoring in progress...</p>
            <p style="font-size:0.875rem;">No scores have been submitted yet. Check back soon!</p>
        </div>
    <?php else: ?>

        <?php
            $top3 = array_slice($leaderboard, 0, 3);
            $rest = array_slice($leaderboard, 3);
            // Arrange podium: 2nd, 1st, 3rd
            $podiumOrder = [];
            if (isset($top3[1])) $podiumOrder[] = $top3[1];
            if (isset($top3[0])) $podiumOrder[] = $top3[0];
            if (isset($top3[2])) $podiumOrder[] = $top3[2];
        ?>

        <?php if(count($leaderboard) >= 2): ?>
        <div class="podium-section">
            <?php foreach($podiumOrder as $entry): ?>
                <?php
                    $rank = $entry->rank;
                    $cardClass = $rank === 1 ? 'podium-1' : ($rank === 2 ? 'podium-2' : 'podium-3');
                    $medal     = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : '🥉');
                    $rankLabel = $rank === 1 ? '1st Place' : ($rank === 2 ? '2nd Place' : '3rd Place');
                ?>
                <div class="podium-card <?= $cardClass ?>">
                    <?php if($rank === 1): ?><div class="crown">👑</div><?php endif; ?>
                    <div class="podium-medal"><?= $medal ?></div>
                    <div class="podium-rank"><?= $rankLabel ?></div>
                    <div class="podium-name"><?= htmlspecialchars((string)($entry->name)) ?></div>
                    <?php if($entry->contestant_number): ?>
                        <div class="podium-num">#<?= htmlspecialchars((string)($entry->contestant_number)) ?></div>
                    <?php endif; ?>
                    <div class="podium-score"><?= htmlspecialchars((string)(number_format((float)$entry->weighted_avg, 2))) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="lb-card">
            <div class="lb-card-header">
                <span class="lb-card-title">Full Rankings</span>
                <span style="font-size:0.7rem;color:#475569;"><?= count($leaderboard) ?> contestant<?= count($leaderboard) !== 1 ? 's' : '' ?> · <?= $judgeCount ?> judge<?= $judgeCount !== 1 ? 's' : '' ?></span>
            </div>
            <div style="overflow-x:auto;">
                <table class="lb-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">Rank</th>
                            <th>Contestant</th>
                            <th class="right">Weighted Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($leaderboard as $entry): ?>
                            <?php $rankClass = $entry->rank === 1 ? 'rank-1' : ($entry->rank === 2 ? 'rank-2' : ($entry->rank === 3 ? 'rank-3' : 'rank-n')); ?>
                            <tr class="<?= $entry->rank === 1 ? 'winner' : '' ?>">
                                <td><span class="rank-badge <?= $rankClass ?>"><?= $entry->rank ?></span></td>
                                <td>
                                    <div class="contestant-name"><?= htmlspecialchars((string)($entry->name)) ?></div>
                                    <?php if($entry->contestant_number): ?>
                                        <div class="contestant-num">Contestant #<?= htmlspecialchars((string)($entry->contestant_number)) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="score-value"><?= htmlspecialchars((string)(number_format((float)$entry->weighted_avg, 2))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

<?php elseif($displayMode === 'criteria' && !empty($displayCriteria)): ?>

    <div style="text-align:center;margin:2rem 0 1.5rem;">
        <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#f59e0b;margin-bottom:0.4rem;">Category</div>
        <h2 style="font-size:1.75rem;font-weight:900;color:#f1f5f9;margin:0;"><?= htmlspecialchars((string)($displayCriteria->name)) ?></h2>
        <div style="font-size:0.8rem;color:#475569;margin-top:0.35rem;">Max: <?= htmlspecialchars((string)($displayCriteria->max_score)) ?> pts</div>
    </div>

    <div class="lb-card">
        <div class="lb-card-header">
            <span class="lb-card-title">Rankings — <?= htmlspecialchars((string)($displayCriteria->name)) ?></span>
        </div>
        <div style="overflow-x:auto;">
            <table class="lb-table">
                <thead>
                    <tr>
                        <th style="width:60px;">Rank</th>
                        <th>Contestant</th>
                        <th class="right">Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($leaderboard as $row): ?>
                        <?php 
                            $rankVal = $row['rank'] ?? 1;
                            $rankClass = $rankVal === 1 ? 'rank-1' : ($rankVal === 2 ? 'rank-2' : ($rankVal === 3 ? 'rank-3' : 'rank-n'));
                        ?>
                        <tr class="<?= $rankVal === 1 ? 'winner' : '' ?>">
                            <td><span class="rank-badge <?= $rankClass ?>"><?= $rankVal ?></span></td>
                            <td>
                                <div class="contestant-name"><?= htmlspecialchars((string)($row['participant']->name)) ?></div>
                            </td>
                            <td class="score-value"><?= htmlspecialchars((string)(number_format((float)$row['total'], 2))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

    <div class="lb-footer">
        <span>Auto-refreshes every <?= $refreshInterval ?> seconds</span> &nbsp;·&nbsp; Powered by FairScore
    </div>
</div>

</body>
</html>
