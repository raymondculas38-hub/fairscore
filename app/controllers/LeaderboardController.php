<?php

class LeaderboardController extends Controller
{
    public function show($eventId)
    {
        $event = Event::findOrFail($eventId);
        $judgeCount = count($event->judges());
        
        $mode = $event->public_display_mode ?? 'overall';
        $criteriaId = $event->public_criteria_id;
        
        if ($mode === 'criteria' && $criteriaId) {
            return $this->showCriteria($event, $criteriaId, $judgeCount);
        }
        
        return $this->showOverall($event, $judgeCount);
    }
    
    private function showOverall(Event $event, $judgeCount)
    {
        $stmt = Model::getDb()->prepare("
            SELECT 
                participants.id,
                participants.name,
                participants.contestant_number,
                SUM(scores.score * criteria.weight) / SUM(criteria.weight) AS weighted_avg,
                COUNT(DISTINCT scores.judge_id) AS judge_count,
                SUM(scores.score) AS total_raw_score
            FROM scores
            JOIN participants ON scores.participant_id = participants.id
            JOIN criteria ON scores.criteria_id = criteria.id
            WHERE scores.event_id = ?
            GROUP BY participants.id, participants.name, participants.contestant_number
            ORDER BY weighted_avg DESC
        ");
        $stmt->execute([$event->id]);
        $leaderboard = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $rank = 1;
        foreach ($leaderboard as $i => $entry) {
            if ($i > 0 && $entry->weighted_avg < $leaderboard[$i - 1]->weighted_avg) {
                $rank = $i + 1;
            }
            $entry->rank = $rank;
        }
        
        $displayMode = 'overall';
        $displayCriteria = null;
        
        return $this->view('public.leaderboard', compact('event', 'leaderboard', 'judgeCount', 'displayMode', 'displayCriteria'));
    }
    
    private function showCriteria(Event $event, $criteriaId, $judgeCount)
    {
        $displayCriteria = Criteria::find($criteriaId);
        $judgesList = $event->judges();
        $participantsList = $event->participants();
        
        $scoresList = Score::where('event_id', '=', $event->id);
        $scoresList = array_filter($scoresList, fn($s) => $s->criteria_id == $criteriaId);
        
        $rows = [];
        foreach ($participantsList as $participant) {
            $pScores = array_filter($scoresList, fn($s) => $s->participant_id == $participant->id);
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
            $rows[] = ['participant' => $participant, 'judgeScores' => $judgeScores, 'total' => $total];
        }
        
        usort($rows, fn($a, $b) => $b['total'] <=> $a['total']);
        
        $rank = 1;
        foreach ($rows as $i => &$r) {
            if ($i > 0 && $r['total'] < $rows[$i - 1]['total']) {
                $rank = $i + 1;
            }
            $r['rank'] = $rank;
        }
        unset($r);
        
        // Convert to objects so we can use -> in views if it expects objects, but the array is fine
        // since we just rewrote it. Actually Blade likely uses object or array.
        // Wait, Laravel collections allow object syntax but array is fine if Blade was used. Since we are replacing Blade, we'll fix views later.
        $leaderboard = $rows;
        $displayMode = 'criteria';
        
        return $this->view('public.leaderboard', compact('event', 'leaderboard', 'judgeCount', 'displayMode', 'displayCriteria'));
    }
}
