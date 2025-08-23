<?php

namespace App\Service;

use App\Repository\GameEventRepository;
use App\Repository\PlayerRepository;
use App\Repository\PlayerTeamAssignmentRepository;
use App\Repository\TeamRepository;

class GameDetailsSyncService
{
    private TeamRepository $teamRepo;
    private PlayerRepository $playerRepo;
    private PlayerTeamAssignmentRepository $ptaRepo;
    private GameEventRepository $gameEventRepo;

    public function __construct(
        TeamRepository $teamRepo,
        PlayerRepository $playerRepo,
        PlayerTeamAssignmentRepository $ptaRepo,
        GameEventRepository $gameEventRepo
    ) {
        $this->teamRepo = $teamRepo;
        $this->playerRepo = $playerRepo;
        $this->ptaRepo = $ptaRepo;
        $this->gameEventRepo = $gameEventRepo;
    }

    /**
     * @param array<string, mixed> $gameDetails
     */
    public function syncGameDetails(array $gameDetails): void
    {
        // Teams
        $homeTeam = $this->teamRepo->findOneBy(['name' => $gameDetails['home_team']['name']]);
        $awayTeam = $this->teamRepo->findOneBy(['name' => $gameDetails['away_team']['name']]);

        foreach ($gameDetails['events'] as $event) {
            $data = $event;
            $data['team'] = $event['team'];
            $data['date'] = $gameDetails['date'];
            $data['competition'] = $gameDetails['competition'];
            $data['result'] = $gameDetails['result'];
            $data['location'] = $gameDetails['location'];
            $data['home_team'] = $homeTeam;
            $data['away_team'] = $awayTeam;

            // Spieler (bei Tor/Karte)
            if (!empty($event['player'])) {
                $player = $this->playerRepo->findOneBy(['name' => $event['player']]);
                $data['player_entity'] = $player;
                // Trikotnummer über PlayerTeamAssignment
                $pta = $this->ptaRepo->findOneBy(['player' => $player, 'team' => 'home' === $event['team'] ? $homeTeam : $awayTeam]);
                $data['shirt_number'] = $pta ? $pta->getShirtNumber() : null;
            }
            // Auswechslung
            if ('substitute' === $event['type']) {
                if (!empty($event['sub_in'])) {
                    $playerIn = $this->playerRepo->findOneBy(['name' => $event['sub_in']]);
                    $ptaIn = $this->ptaRepo->findOneBy(['player' => $playerIn, 'team' => 'home' === $event['team'] ? $homeTeam : $awayTeam]);
                    $data['sub_in_entity'] = $playerIn;
                    $data['sub_in_shirt_number'] = $ptaIn ? $ptaIn->getShirtNumber() : null;
                }
                if (!empty($event['sub_out'])) {
                    $playerOut = $this->playerRepo->findOneBy(['name' => $event['sub_out']]);
                    $ptaOut = $this->ptaRepo->findOneBy(['player' => $playerOut, 'team' => 'home' === $event['team'] ? $homeTeam : $awayTeam]);
                    $data['sub_out_entity'] = $playerOut;
                    $data['sub_out_shirt_number'] = $ptaOut ? $ptaOut->getShirtNumber() : null;
                }
            }
            // GameEvent anlegen/aktualisieren
            $this->gameEventRepo->updateOrCreateFromCrawler($data);
        }
    }
}
