<?php

namespace Tests\Mocks\Repository;

use App\Repository\GameEventRepository;

class GameEventRepositoryMock extends GameEventRepository
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $calls = [];

    public function updateOrCreateFromCrawler(array $data): void // array<string, mixed> $data
    {
        $this->calls[] = $data;
    }
}
