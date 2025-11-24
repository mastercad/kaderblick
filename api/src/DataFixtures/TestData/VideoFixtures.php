<?php

namespace App\DataFixtures\TestData;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class VideoFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            GameFixtures::class,
            VideoTypeFixtures::class,
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $games = $manager->getRepository(Game::class)->findAll();
        $videoTypes = $manager->getRepository(VideoType::class)->findAll();
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'user16@example.com']);

        if (empty($games)) {
            throw new RuntimeException('Keine Games gefunden für Video-Fixtures');
        }

        if (empty($videoTypes)) {
            throw new RuntimeException('Keine VideoTypes gefunden für Video-Fixtures');
        }

        if (!$user) {
            throw new RuntimeException('User user16@example.com nicht gefunden');
        }

        // Erstelle für jedes Game 2-3 Videos
        $videoIndex = 0;
        foreach ($games as $gameIndex => $game) {
            $videoCount = rand(2, 3);

            for ($i = 0; $i < $videoCount; ++$i) {
                $video = new Video();
                $video->setName('Video ' . ($videoIndex + 1) . ' - Spiel ' . ($gameIndex + 1));
                $video->setFilePath('videos/game_' . $game->getId() . '_video_' . ($i + 1) . '.mp4');
                $video->setGame($game);
                $video->setVideoType($videoTypes[array_rand($videoTypes)]);
                $video->setCreatedFrom($user);
                $video->setCreatedAt(new DateTimeImmutable());
                $video->setUpdatedAt(new DateTimeImmutable());
                $video->setLength(rand(300, 7200)); // 5 Minuten bis 2 Stunden
                $video->setSort($i + 1);

                $manager->persist($video);
                $this->addReference('video_' . $videoIndex, $video);
                ++$videoIndex;
            }
        }

        $manager->flush();
    }
}
