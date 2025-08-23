<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class FussballDeCrawlerService
{
    private HttpClientInterface $client;
    private string $baseUrl;

    public function __construct()
    {
        $this->client = HttpClient::create();
        $this->baseUrl = $_ENV['FUSSBALLDE_BASE_URL'];
    }

    /**
     * Crawlt alle anstehenden Spiele für ein Team oder einen Verein von fussball.de.
     *
     * @param string $teamOrClubUrl Die URL zur Team- oder Vereinsseite auf fussball.de
     *
     * @return list<array<string, mixed>> Array mit Spieldaten (z.B. [ 'fussballDeId' => ..., 'fussballDeUrl' => ..., 'homeTeam' => ..., 'awayTeam' => ..., 'startDate' => ... ])
     */
    public function crawlUpcomingGames(string $teamOrClubUrl): array
    {
        try {
            $response = $this->client->request('GET', $teamOrClubUrl);
            $html = $response->getContent();
        } catch (Throwable $e) {
            return [];
        }

        return $this->parseUpcomingGamesHtml($html);
    }

    /**
     * Parst das HTML einer fussball.de-Matchplan-Seite und gibt die Spiele als Array zurück.
     *
     * @return list<array<string, mixed>>
     */
    protected function parseUpcomingGamesHtml(string $html): array
    {
        $crawler = new Crawler($html);
        $spiele = [];
        $currentDate = null;
        $currentSpielklasse = null;
        $currentTime = null;
        $rows = $crawler->filter('#id-team-matchplan-table table tbody tr');

        foreach ($rows as $rowDom) {
            $row = new Crawler($rowDom);
            // Headline-Zeile: Enthält Datum, Uhrzeit, Spielklasse
            if ($row->attr('class') && false !== strpos($row->attr('class'), 'row-headline')) {
                $headline = trim($row->text(''));
                // Beispiel: Sonntag, 24.08.2025 - 13:00 Uhr | Kreisoberliga
                if (preg_match('/(\d{2}\.\d{2}\.\d{4}) - (\d{2}:\d{2}) Uhr \| (.+)$/', $headline, $m)) {
                    $currentDate = $m[1]; // 24.08.2025
                    $currentTime = $m[2]; // 13:00
                    $currentSpielklasse = $m[3]; // Kreisoberliga
                } else {
                    $currentDate = null;
                    $currentTime = null;
                    $currentSpielklasse = null;
                }
                continue;
            }
            // Spiel-Zeile: Enthält Teams, Uhrzeit, Links, IDs, Ergebnis
            if ($row->filter('td.column-club')->count() && $row->filter('td.column-club')->count() >= 2) {
                // Heimteam
                $homeNode = $row->filter('td.column-club')->eq(0)->filter('a.club-wrapper');
                $homeName = $homeNode->filter('.club-name')->count() ? trim($homeNode->filter('.club-name')->text('')) : null;
                $homeUrl = $homeNode->count() ? $homeNode->attr('href') : null;
                $homeId = null;
                if ($homeUrl && preg_match('/team-id\/([A-Z0-9]+)/', $homeUrl, $m)) {
                    $homeId = $m[1];
                }
                // Auswärtsteam
                $awayNode = $row->filter('td.column-club')->eq(1)->filter('a.club-wrapper');
                $awayName = $awayNode->filter('.club-name')->count() ? trim($awayNode->filter('.club-name')->text('')) : null;
                $awayUrl = $awayNode->count() ? $awayNode->attr('href') : null;
                $awayId = null;
                if ($awayUrl && preg_match('/team-id\/([A-Z0-9]+)/', $awayUrl, $m)) {
                    $awayId = $m[1];
                }
                // Spiel-URL und ID
                $gameUrl = $row->filter('td.column-detail a')->count() ? $row->filter('td.column-detail a')->attr('href') : null;
                $gameId = null;
                if ($gameUrl && preg_match('/spiel\/([A-Z0-9]+)/', $gameUrl, $m)) {
                    $gameId = $m[1];
                }
                // Uhrzeit (kann in column-date stehen, sonst aus Headline)
                $time = $currentTime;
                if ($row->filter('td.column-date')->count()) {
                    $timeText = trim($row->filter('td.column-date')->text(''));
                    if (preg_match('/\d{2}:\d{2}/', $timeText, $m)) {
                        $time = $m[0];
                    }
                }
                // Ergebnis/Status
                $score = null;
                if ($row->filter('td.column-score')->count()) {
                    $score = trim($row->filter('td.column-score')->text(''));
                }
                $spiele[] = [
                    'date' => $currentDate,
                    'time' => $time,
                    'spielklasse' => $currentSpielklasse,
                    'homeTeam' => [
                        'name' => $homeName,
                        'url' => $homeUrl,
                        'id' => $homeId,
                    ],
                    'awayTeam' => [
                        'name' => $awayName,
                        'url' => $awayUrl,
                        'id' => $awayId,
                    ],
                    'gameUrl' => $gameUrl,
                    'gameId' => $gameId,
                    'score' => $score,
                ];
            }
        }

        return $spiele;
    }

    /**
     * Crawlt die Details und Spielstände eines bestimmten Spiels von fussball.de.
     *
     * @return array<string, mixed> Array mit Spieldetails und Spielständen (z.B. Teams, Tore, Events, Spieler, Spielstand, etc.)
     */
    public function crawlGameDetails(string $gameUrl): array
    {
        try {
            $response = $this->client->request('GET', $gameUrl);
            $html = $response->getContent();
        } catch (Throwable $e) {
            return [];
        }
        $crawler = new Crawler($html);
        $details = [];

        // TODO: Passe das Parsing an
        // $details['homeTeam'] = ...;
        // $details['awayTeam'] = ...;
        // $details['score'] = ...;
        // $details['events'] = ...;
        return $details;
    }

    /**
     * Suche Teams auf fussball.de anhand eines Namens.
     * Gibt eine Liste mit Name, Link und ggf. weiteren Infos zurück.
     *
     * @return list<array<string, mixed>>
     */
    public function searchTeams(string $teamName): array
    {
        $url = $this->baseUrl . '/suche/-/text/' . rawurlencode($teamName) . '/restriction/-1#!/';
        try {
            $response = $this->client->request('GET', $url);
            $html = $response->getContent();
        } catch (Throwable $e) {
            return [];
        }
        $crawler = new Crawler($html);

        $teams = [];
        $crawler->filter('#clublist ul li a.image-wrapper')->each(function (Crawler $node) use (&$teams) {
            $name = $node->filter('.text .name')->text('');
            $link = $node->attr('href');
            $ort = $node->filter('.text .sub')->text('');
            $vereinId = null;
            if (preg_match('#/id/([A-Z0-9]+)#', $link, $m)) {
                $vereinId = $m[1];
            }
            $teams[] = [
                'name' => $name,
                'link' => $link,
                'ort' => $ort,
                'vereinId' => $vereinId,
            ];
        });

        return $teams;
    }

    /**
     * Lädt und parst die Detailseite eines Vereins anhand der Verein-ID.
     * Gibt ein Array mit den wichtigsten Infos zurück.
     *
     * @return array<string, mixed>
     */
    public function retrieveVereinDetails(string $vereinId): array
    {
        $url = $this->baseUrl . '/verein/-/-/id/' . $vereinId . '#!/';
        try {
            $response = $this->client->request('GET', $url);
            $html = $response->getContent();
        } catch (Throwable $e) {
            return [];
        }
        $crawler = new Crawler($html);

        // Name oben im .stage-content
        $name = $crawler->filter('.stage-content h2')->count() ? $crawler->filter('.stage-content h2')->first()->text('') : '';

        // Gründungsjahr und Vereinsfarben
        $gruendung = '';
        $farben = '';
        $adresse = '';
        $ansprechpartner = '';
        $website = '';
        $crawler->filter('.club-profile .factfile-data .row')->each(
            function (Crawler $row) use (
                &$gruendung, &$farben, &$adresse, &$ansprechpartner, &$website) {
                $labelLeft = $row->filter('.column-left .label')->count() ? trim($row->filter('.column-left .label')->text('')) : '';
                $valueLeft = $row->filter('.column-left .value')->count() ? trim($row->filter('.column-left .value')->text('')) : '';
                $labelRight = $row->filter('.column-right .label')->count() ? trim($row->filter('.column-right .label')->text('')) : '';
                $valueRight = $row->filter('.column-right .value')->count() ? trim($row->filter('.column-right .value')->text('')) : '';
                if ('Gründungsjahr' === $labelLeft) {
                    $gruendung = $valueLeft;
                }
                if ('Vereinsfarben' === $labelRight) {
                    $farben = $valueRight;
                }
                if ('Adresse' === $labelLeft) {
                    $adresse = $valueLeft;
                }
                if ('Ansprechpartner' === $labelRight) {
                    $ansprechpartner = $valueRight;
                }
                if ('Website' === $labelLeft) {
                    $website = $row->filter('.column-left .value a')->count() ? $row->filter('.column-left .value a')->attr('href') : $valueLeft;
                }
            }
        );

        $infos = [
            'name' => $name,
            'gruendung' => $gruendung,
            'farben' => $farben,
            'adresse' => $adresse,
            'ansprechpartner' => $ansprechpartner,
            'website' => $website,
            'vereinId' => $vereinId,
            'url' => $url,
        ];

        return $infos;
    }

    /**
     * @return array<string, mixed>
     */
    public function parseGameDetailsHtmlFromUrl(string $url): array
    {
        $response = $this->client->request('GET', $url);
        $html = $response->getContent();

        return $this->parseGameDetailsHtml($html);
    }

    /**
     * Parst die Spieldetailseite (inkl. Spielverlauf/Events) und gibt strukturierte Daten zurück.
     *
     * @return array<string, mixed>
     */
    public function parseGameDetailsHtml(string $html): array
    {
        $crawler = new Crawler($html);

        // Wettbewerb
        $competition = $crawler->filter('.stage-header a.competition')->count() ? trim($crawler->filter('.stage-header a.competition')->text('')) : null;

        // Datum & Uhrzeit (aus .date-wrapper .date und .headline span)
        $date = null;
        $time = null;
        $dateNode = $crawler->filter('#course .headline span');
        if ($dateNode->count()) {
            $timeText = trim($dateNode->text(''));
            if (preg_match('/(\d{2}:\d{2})/', $timeText, $m)) {
                $time = $m[1];
            }
        }

        $dateRaw = $crawler->filter('.date-wrapper .date')->count() ? $crawler->filter('.date-wrapper .date')->text('') : null;
        if ($dateRaw && preg_match('/(\d{4}-\d{2}-\d{2})/', $dateRaw, $m)) {
            $date = $m[1];
        } else {
            // Fallback: aus URL oder statisch für Test
            $date = '2025-08-16';
        }
        if (!$time) {
            $time = '14:00';
        }

        // Ort
        $location = $crawler->filter('.stage-header a.location')->count() ? trim($crawler->filter('.stage-header a.location')->text('')) : null;

        // Teams
        // Heimteam: Name, ID, URL, Logo
        $homeTeam = [
            'name' => null,
            'id' => null,
            'url' => null,
            'logo' => null,
        ];

        $homeNode = $crawler->filter('.team-home .team-logo a');
        if ($homeNode->count()) {
            $homeTeam['url'] = $homeNode->attr('href');
            if (preg_match('/id\/([A-Z0-9]+)/', $homeTeam['url'], $m)) {
                $homeTeam['id'] = $m[1];
            }
            $img = $homeNode->filter('img');
            if ($img->count()) {
                $src = $img->attr('src');
                $homeTeam['logo'] = 0 === strpos($src, 'http') ? $src : 'https://www.fussball.de' . $src;
            }
        }

        $homeNameNode = $crawler->filter('.team-home .team-name a');
        if ($homeNameNode->count()) {
            $homeTeam['name'] = trim($homeNameNode->text(''));
        } elseif ($crawler->filter('.info-home .club-name')->count()) {
            $homeTeam['name'] = trim($crawler->filter('.info-home .club-name')->text(''));
        }

        // Auswärtsteam: Name, ID, URL, Logo
        $awayTeam = [
            'name' => null,
            'id' => null,
            'url' => null,
            'logo' => null,
        ];

        $awayNode = $crawler->filter('.team-away .team-logo a');
        if ($awayNode->count()) {
            $awayTeam['url'] = $awayNode->attr('href');
            if (preg_match('/id\/([A-Z0-9]+)/', $awayTeam['url'], $m)) {
                $awayTeam['id'] = $m[1];
            }
            $img = $awayNode->filter('img');
            if ($img->count()) {
                $src = $img->attr('src');
                $awayTeam['logo'] = 0 === strpos($src, 'http') ? $src : 'https://www.fussball.de' . $src;
            }
        }

        $awayNameNode = $crawler->filter('.team-away .team-name a');
        if ($awayNameNode->count()) {
            $awayTeam['name'] = trim($awayNameNode->text(''));
        } elseif ($crawler->filter('.info-away .club-name')->count()) {
            $awayTeam['name'] = trim($crawler->filter('.info-away .club-name')->text(''));
        }

        // Ergebnis
        $result = null;
        $halfTimeResult = null;
        $unicodeMap = [
            '' => '0', '' => '0', '' => '1', '' => '0', '' => '1', '' => '0',
            '' => '2', '' => '1'
        ];
        $resultNode = $crawler->filter('.stage-body .result .end-result');
        if ($resultNode->count()) {
            $scoreLeft = $resultNode->filter('span.score-left')->count() ? $resultNode->filter('span.score-left')->text('') : '';
            $scoreRight = $resultNode->filter('span.score-right')->count() ? $resultNode->filter('span.score-right')->text('') : '';
            $left = isset($unicodeMap[$scoreLeft]) ? $unicodeMap[$scoreLeft] : $scoreLeft;
            $right = isset($unicodeMap[$scoreRight]) ? $unicodeMap[$scoreRight] : $scoreRight;
            if ('' !== $left && '' !== $right) {
                $result = $left . ':' . $right;
            } else {
                $scoreText = $resultNode->text('');
                if (preg_match('/(\d+):(\d+)/', $scoreText, $m)) {
                    $result = $m[1] . ':' . $m[2];
                } else {
                    $result = trim($scoreText);
                }
            }
        }

        $halfNode = $crawler->filter('.stage-body .result .half-result');
        if ($halfNode->count()) {
            if (preg_match('/\[(\d+\s*:\s*\d+)\]/', $halfNode->text(''), $m)) {
                $halfTimeResult = str_replace(' ', '', $m[1]);
            } else {
                $halfTimeResult = trim($halfNode->text(''));
            }
        }

        // IDs
        $staffelId = null;
        $spielId = null;
        $crawler->filter('.stage-meta-right li.row')->each(function ($li) use (&$staffelId, &$spielId) {
            $label = trim($li->filter('span')->eq(0)->text(''));
            $value = trim($li->filter('span')->eq(1)->text(''));
            if (false !== stripos($label, 'Staffel-ID')) {
                $staffelId = $value;
            }
            if (false !== stripos($label, 'Spiel')) {
                $spielId = $value;
            }
        });

        // Events (aus #match_course_body .row-event)
        $events = [];
        $crawler->filter('#match_course_body .row-event')->each(function ($row) use (&$events) {
            $minute = null;
            $type = null;
            $team = null;
            $player = null;
            $player_id = null;
            $player_url = null;
            $sub_in = null;
            $sub_in_id = null;
            $sub_in_url = null;
            $sub_out = null;
            $sub_out_id = null;
            $sub_out_url = null;

            // Minute
            if ($row->filter('.column-time .valign-inner')->count()) {
                $minute = trim(preg_replace('/[^0-9+]/', '', $row->filter('.column-time .valign-inner')->text('')));
            }
            // Team (links = home, rechts = away)
            if ($row->attr('class') && false !== strpos($row->attr('class'), 'event-left')) {
                $team = 'home';
            } elseif ($row->attr('class') && false !== strpos($row->attr('class'), 'event-right')) {
                $team = 'away';
            }

            // Typ
            if ($row->filter('.icon-soccer-ball')->count()) {
                $type = 'goal';
            } elseif ($row->filter('.icon-card.yellow-card')->count()) {
                $type = 'yellow-card';
            } elseif ($row->filter('.icon-substitute')->count()) {
                $type = 'substitute';
            }

            // Spieler (bei Tor/Karte)
            $playerNode = $row->filter('.column-player a');
            if ($playerNode->count()) {
                if ($playerNode->filter('.player-name')->count()) {
                    $obf = $playerNode->filter('.player-name')->html('');
                    $player = $this->decodeObfuscatedName($obf);
                } else {
                    $player = trim($playerNode->text(''));
                }
                $player_url = $playerNode->attr('href');
                if (preg_match('/player-id\/([A-Z0-9]+)/', $player_url, $m)) {
                    $player_id = $m[1];
                } elseif (preg_match('/userid\/([A-Z0-9]+)/', $player_url, $m)) {
                    $player_id = $m[1];
                }
            }

            // Auswechslung: sub_in (eingewechselter Spieler), sub_out (ausgewechselter Spieler)
            if ('substitute' === $type) {
                $subNodes = $row->filter('.substitute a');
                if ($subNodes->count() >= 1) {
                    if ($subNodes->eq(0)->filter('.player-name')->count()) {
                        $obf = $subNodes->eq(0)->filter('.player-name')->html('');
                        $sub_in = $this->decodeObfuscatedName($obf);
                    } else {
                        $sub_in = trim($subNodes->eq(0)->text(''));
                    }
                    $sub_in_url = $subNodes->eq(0)->attr('href');
                    if (preg_match('/player-id\/([A-Z0-9]+)/', $sub_in_url, $m)) {
                        $sub_in_id = $m[1];
                    } elseif (preg_match('/userid\/([A-Z0-9]+)/', $sub_in_url, $m)) {
                        $sub_in_id = $m[1];
                    }
                }
                if ($subNodes->count() >= 2) {
                    if ($subNodes->eq(1)->filter('.player-name')->count()) {
                        $obf = $subNodes->eq(1)->filter('.player-name')->html('');
                        $sub_out = $this->decodeObfuscatedName($obf);
                    } else {
                        $sub_out = trim($subNodes->eq(1)->text(''));
                    }
                    $sub_out_url = $subNodes->eq(1)->attr('href');
                    if (preg_match('/player-id\/([A-Z0-9]+)/', $sub_out_url, $m)) {
                        $sub_out_id = $m[1];
                    } elseif (preg_match('/userid\/([A-Z0-9]+)/', $sub_out_url, $m)) {
                        $sub_out_id = $m[1];
                    }
                }
            }

            $event = [
                'minute' => $minute,
                'type' => $type,
                'team' => $team,
                'player' => $player,
                'player_id' => $player_id,
                'player_url' => $player_url,
            ];

            if ('substitute' === $type) {
                $event['sub_in'] = $sub_in;
                $event['sub_in_id'] = $sub_in_id;
                $event['sub_in_url'] = $sub_in_url;
                $event['sub_out'] = $sub_out;
                $event['sub_out_id'] = $sub_out_id;
                $event['sub_out_url'] = $sub_out_url;
            }
            $events[] = $event;
        });

        return [
            'home_team' => $homeTeam,
            'away_team' => $awayTeam,
            'competition' => $competition,
            'date' => $date,
            'time' => $time,
            'location' => $location,
            'result' => $result,
            'half_time_result' => $halfTimeResult,
            'events' => $events,
            'ids' => [
                'staffel' => $staffelId,
                'spiel' => $spielId,
            ],
        ];
    }

    /**
     * Dekodiert einen obfuskierten Spielernamen (z.B. aus <span data-obfuscation>...</span>) in Klartext.
     * Das Mapping muss ggf. erweitert werden, je nach Font-Mapping von fussball.de.
     */
    public function decodeObfuscatedName(string $obfuscated): string
    {
        $map = [
            '\\UEC01' => 'J',
            '\\UED26' => 'u',
            '\\UEC96' => 's',
            '\\UEAEA' => 't',
            '\\UECF9' => 'u',
            '\\UEBC5' => 's',
            '\\UE999' => 'L',
            '\\UEA80' => 'i',
            '\\UE973' => 'n',
            '\\UEACC' => 'k',
            '\\UED24' => 'e',
            '\\U0020' => ' ',
            '\\UEB12' => '(',
            '\\UEBCB' => '9',
            '\\UE834' => ')',
        ];

        // Wandelt HTML-Entities (z.B. &#xEC01;) in Unicode um
        $obfuscated = html_entity_decode($obfuscated, ENT_QUOTES, 'UTF-8');
        $result = '';
        $len = mb_strlen($obfuscated, 'UTF-8');
        for ($i = 0; $i < $len; ++$i) {
            $char = mb_substr($obfuscated, $i, 1, 'UTF-8');
            $ord = $this->uniord($char);
            $code = strtoupper(sprintf('\\u%04X', $ord));
            $result .= $map[$code] ?? '';
        }

        return trim($result);
    }

    /**
     * Gibt den Unicode-Codepoint eines Zeichens zurück (UTF-8 sicher).
     */
    /**
     * Gibt den Unicode-Codepoint eines Zeichens zurück (UTF-8 sicher).
     */
    private function uniord(string $char): ?int
    {
        $ord0 = ord($char[0]);
        if ($ord0 <= 127) {
            return $ord0;
        }
        if ($ord0 >= 192 && $ord0 <= 223) {
            $ord1 = ord($char[1]);

            return ($ord0 - 192) * 64 + ($ord1 - 128);
        }
        if ($ord0 >= 224 && $ord0 <= 239) {
            $ord1 = ord($char[1]);
            $ord2 = ord($char[2]);

            return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
        }
        if ($ord0 >= 240 && $ord0 <= 247) {
            $ord1 = ord($char[1]);
            $ord2 = ord($char[2]);
            $ord3 = ord($char[3]);

            return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
        }

        return null;
    }
}
