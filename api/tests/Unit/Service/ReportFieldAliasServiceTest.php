<?php

namespace App\Tests\Unit\Service;

use App\Service\ReportFieldAliasService;
use PHPUnit\Framework\TestCase;

class ReportFieldAliasServiceTest extends TestCase
{
    public function testFieldAliasesReturnsArrayWithoutEntityManager(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertNotEmpty($aliases);
    }

    public function testFieldAliasesContainsDimensionFields(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $expectedDimensions = ['player', 'team', 'eventType', 'gameDate', 'month', 'gameType', 'position', 'homeAway', 'surfaceType'];

        foreach ($expectedDimensions as $dim) {
            $this->assertArrayHasKey($dim, $aliases, "Missing dimension alias: $dim");
            $this->assertSame('dimension', $aliases[$dim]['category'], "Expected '$dim' to be a dimension");
        }
    }

    public function testFieldAliasesContainsMetricFields(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $expectedMetrics = ['goals', 'assists', 'shots', 'yellowCards', 'redCards', 'fouls', 'passes', 'tackles', 'interceptions', 'saves'];

        foreach ($expectedMetrics as $metric) {
            $this->assertArrayHasKey($metric, $aliases, "Missing metric alias: $metric");
            $this->assertSame('metric', $aliases[$metric]['category'], "Expected '$metric' to be a metric");
        }
    }

    public function testEachAliasHasLabel(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        foreach ($aliases as $key => $alias) {
            $this->assertArrayHasKey('label', $alias, "Alias '$key' is missing a label");
            $this->assertNotEmpty($alias['label'], "Alias '$key' has an empty label");
        }
    }

    public function testEachAliasHasCategory(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        foreach ($aliases as $key => $alias) {
            $this->assertArrayHasKey('category', $alias, "Alias '$key' is missing category");
            $this->assertContains($alias['category'], ['dimension', 'metric'], "Alias '$key' has invalid category");
        }
    }

    public function testMetricAliasesHaveAggregateCallable(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        foreach ($aliases as $key => $alias) {
            if ('metric' === $alias['category']) {
                $this->assertArrayHasKey('aggregate', $alias, "Metric '$key' is missing 'aggregate'");
                $this->assertTrue(is_callable($alias['aggregate']), "Metric '$key' aggregate is not callable");
            }
        }
    }

    public function testDimensionAliasesHaveAccessibleFromEventFlag(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        foreach ($aliases as $key => $alias) {
            if ('dimension' === $alias['category']) {
                $this->assertArrayHasKey('accessibleFromEvent', $alias, "Dimension '$key' missing 'accessibleFromEvent' flag");
                $this->assertTrue($alias['accessibleFromEvent'], "Dimension '$key' should be accessible from event");
            }
        }
    }

    public function testPlayerAliasHasJoinHint(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertSame(['player'], $aliases['player']['joinHint']);
    }

    public function testTeamAliasHasJoinHint(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertSame(['team'], $aliases['team']['joinHint']);
    }

    public function testEventTypeAliasHasJoinHint(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertSame(['gameEventType'], $aliases['eventType']['joinHint']);
    }

    public function testSurfaceTypeAliasHasJoinHint(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertSame(['game', 'location', 'surfaceType'], $aliases['surfaceType']['joinHint']);
    }

    public function testHomeAwayHasNullJoinHint(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertNull($aliases['homeAway']['joinHint']);
    }

    public function testWeatherDimensionsHaveNullJoinHint(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $weatherDimensions = ['weatherCondition', 'temperatureRange', 'windStrength', 'cloudCover'];
        foreach ($weatherDimensions as $dim) {
            $this->assertNull($aliases[$dim]['joinHint'], "Weather dimension '$dim' should have null joinHint");
        }
    }

    public function testGoalsAliasLabel(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertSame('Tore', $aliases['goals']['label']);
    }

    public function testPlayerAliasLabel(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertSame('Spieler', $aliases['player']['label']);
    }

    public function testEventTypeValueCallbackReturnsNullForNullType(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $ev = new class {
            public function getGameEventType(): mixed
            {
                return null;
            }
        };

        $result = ($aliases['eventType']['value'])($ev);
        $this->assertNull($result);
    }

    public function testEventTypeValueCallbackReturnsName(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $type = new class {
            public function getName(): string
            {
                return 'Tor';
            }
        };

        $ev = new class {
            public mixed $type;

            public function getGameEventType(): mixed
            {
                return $this->type;
            }
        };
        $ev->type = $type;

        $result = ($aliases['eventType']['value'])($ev);
        $this->assertSame('Tor', $result);
    }

    public function testPlayerAliasHasPathWithFieldAndSubfield(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $this->assertSame(['player', 'fullName'], $aliases['player']['path']);
    }

    public function testShotAccuracyReturnsZeroForNoShots(): void
    {
        $aliases = ReportFieldAliasService::fieldAliases(null);

        $result = ($aliases['shotAccuracy']['aggregate'])([]);
        $this->assertSame(0, $result);
    }
}
