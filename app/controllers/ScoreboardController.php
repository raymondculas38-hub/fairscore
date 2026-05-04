<?php

class ScoreboardController extends Controller
{
    private function checkAuth()
    {
        if (!auth() || !auth()->isAdmin()) {
            $this->redirect(url('/admin/login'));
        }
    }

    public function index()
    {
        $this->checkAuth();
        $events = Event::scopedAll();
        
        foreach ($events as $event) {
            $event->participants_count = count($event->participants());
            $event->judges_count = count($event->judges());
            $event->criteria_count = count($event->criteria());
            $event->scores_count = count($event->scores());
        }
        
        usort($events, fn($a, $b) => strtotime($b->created_at) - strtotime($a->created_at));
        
        return $this->view('admin.scoreboard.index', compact('events'));
    }

    public function show($eventId)
    {
        $this->checkAuth();
        $event = Event::scopedFindOrFail($eventId);
        
        $criteriaList = $event->criteria();
        $judgesList = $event->judges();
        $participantsList = $event->participants();
        $scoresList = $event->scores();
        
        $breakdown = [];
        
        foreach ($criteriaList as $criterion) {
            $catData = [
                'criterion' => $criterion,
                'participants' => []
            ];
            
            foreach ($participantsList as $participant) {
                $pScores = array_filter($scoresList, fn($s) => $s->participant_id == $participant->id && $s->criteria_id == $criterion->id);
                
                $judgeScores = [];
                $total = 0;
                
                foreach ($judgesList as $judge) {
                    $s = null;
                    foreach ($pScores as $scoreObj) {
                        if ($scoreObj->judge_id == $judge->id) {
                            $s = $scoreObj;
                            break;
                        }
                    }
                    $val = $s ? (float)$s->score : null;
                    $judgeScores[$judge->id] = $val;
                    $total += ($val ?? 0);
                }
                
                $catData['participants'][] = [
                    'participant' => $participant,
                    'judgeScores' => $judgeScores,
                    'total' => $total,
                    'rank' => 0,
                ];
            }
            
            usort($catData['participants'], fn($a, $b) => $b['total'] <=> $a['total']);
            
            $rank = 1;
            foreach ($catData['participants'] as $i => &$p) {
                if ($i > 0 && $p['total'] < $catData['participants'][$i - 1]['total']) {
                    $rank = $i + 1;
                }
                $p['rank'] = $rank;
            }
            
            $breakdown[$criterion->id] = $catData;
        }

        // Overall Leaderboard
        $stmt = Model::getDb()->prepare("
            SELECT 
                p.id,
                p.name,
                p.contestant_number,
                COALESCE(SUM(s.score * c.weight) / NULLIF(SUM(c.weight), 0), 0) AS weighted_avg,
                COALESCE(SUM(s.score), 0) AS total_raw_score
            FROM participants p
            LEFT JOIN scores s ON s.participant_id = p.id AND s.event_id = ?
            LEFT JOIN criteria c ON s.criteria_id = c.id
            WHERE p.event_id = ?
            GROUP BY p.id, p.name, p.contestant_number
            ORDER BY weighted_avg DESC, total_raw_score DESC
        ");
        $stmt->execute([$event->id, $event->id]);
        $overallLeaderboard = $stmt->fetchAll(PDO::FETCH_OBJ);

        $rank = 1;
        foreach ($overallLeaderboard as $i => $entry) {
            if ($i > 0 && $entry->weighted_avg < $overallLeaderboard[$i - 1]->weighted_avg) {
                $rank = $i + 1;
            }
            $entry->rank = $rank;
        }

        // Category winners
        $categoryWinners = [];
        foreach ($breakdown as $critId => $data) {
            $winner = $data['participants'][0] ?? null;
            if ($winner) {
                $categoryWinners[$critId] = [
                    'criterion' => $data['criterion'],
                    'winner' => $winner['participant'],
                    'total' => $winner['total']
                ];
            }
        }

        return $this->view('admin.scoreboard.show', compact('event', 'breakdown', 'overallLeaderboard', 'categoryWinners'));
    }

    public function setDisplay($eventId)
    {
        $this->checkAuth();
        check_csrf();
        
        $event = Event::scopedFindOrFail($eventId);
        
        $mode = $_POST['mode'] ?? 'overall';
        $criteria_id = $_POST['criteria_id'] ?? null;
        
        if (!in_array($mode, ['overall', 'criteria'])) {
            die("Invalid mode");
        }
        
        $event->update([
            'public_display_mode' => $mode,
            'public_criteria_id' => $mode === 'criteria' ? ($criteria_id ?: null) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $label = 'Overall Rankings';
        if ($mode === 'criteria' && $criteria_id) {
            $c = Criteria::find($criteria_id);
            if ($c) $label = $c->name;
        }
        
        with('success', "Public scoreboard now shows: {$label}");
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
