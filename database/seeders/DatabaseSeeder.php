<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Criteria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin account ────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Administrator',
            'username' => 'admin',
            'email'    => 'admin@fairscore.local',
            'password' => Hash::make('admin123'),
            'role'     => 'ADMIN',
        ]);

        // ── Judge accounts ───────────────────────────────────────────────────
        $judges = [];
        foreach (['Judge Alpha', 'Judge Beta', 'Judge Gamma'] as $i => $name) {
            $judges[] = User::create([
                'name'     => $name,
                'username' => 'judge' . ($i + 1),
                'email'    => null,
                'password' => Hash::make('judge123'),
                'role'     => 'JUDGE',
            ]);
        }

        // ── Sample event ─────────────────────────────────────────────────────
        $event = Event::create([
            'name'        => 'FairScore Grand Championship',
            'description' => 'The inaugural FairScore demo event. Three judges, five contestants.',
            'event_date'  => now()->addDays(2),
            'status'      => 'upcoming',
        ]);

        // Assign all judges to the event
        $event->judges()->attach(array_map(fn($j) => $j->id, $judges));

        // ── Participants ──────────────────────────────────────────────────────
        $participantNames = ['Maria Santos', 'Jose Reyes', 'Ana dela Cruz', 'Carlos Ramos', 'Liza Torres'];
        foreach ($participantNames as $i => $name) {
            Participant::create([
                'event_id'          => $event->id,
                'name'              => $name,
                'contestant_number' => $i + 1,
            ]);
        }

        // ── Scoring Criteria ─────────────────────────────────────────────────
        $criteriaData = [
            ['name' => 'Talent',       'max_score' => 40, 'weight' => 1.5],
            ['name' => 'Q&A',          'max_score' => 30, 'weight' => 1.0],
            ['name' => 'Presentation', 'max_score' => 20, 'weight' => 0.8],
            ['name' => 'Audience Vote','max_score' => 10, 'weight' => 0.7],
        ];
        foreach ($criteriaData as $c) {
            Criteria::create(array_merge($c, ['event_id' => $event->id]));
        }
    }
}
