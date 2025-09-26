<?php

namespace App\Service;

use App\Entity\CalendarEvent;
use App\Entity\WeatherData;
use App\Repository\CalendarEventRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

final class WeatherService
{
    /** @var array<string> */
    private array $hourlySettings = [
        'temperature_2m',
        'apparent_temperature',
        'precipitation',
        'precipitation_probability',
        'wind_speed_10m',
        'wind_gusts_10m',
        'wind_direction_10m',
        'uv_index',
        'cloudcover',
        'relative_humidity_2m',
        'pressure_msl,weathercode'
    ];

    /** @var array<string> */
    private array $dailySettings = [
        'weathercode',
        'temperature_2m_max',
        'temperature_2m_min',
        'apparent_temperature_max',
        'apparent_temperature_min',
        'sunrise',
        'sunset',
        'precipitation_sum',
        'windspeed_10m_max',
        'windgusts_10m_max',
        'uv_index_max',
        'wind_speed_10m_max',
        'wind_gusts_10m_max',
        'cloudcover_mean'
    ];

    public function __construct(private string $weatherApiUrl, private EntityManagerInterface $entityManager)
    {
    }

    public function retrieveWeatherData(): self
    {
        /** @var CalendarEventRepository $calendarEventRepository */
        $calendarEventRepository = $this->entityManager->getRepository(CalendarEvent::class);
        $now = new DateTimeImmutable();
        $daysBack = $now->modify('-90 days');
        $daysForward = $now->modify('+14 days');

        $calendarEvents = $calendarEventRepository->findUpcoming();

        foreach ($calendarEvents as $event) {
            if ($event->getStartDate() < $daysBack || $event->getStartDate() > $daysForward) {
                echo 'EVENT ' . $event->getStartDate()->format('Y-m-d') . ' NICHT IN RANGE!';
                continue;
            }

            if (null === $event->getLocation() || null === $event->getLocation()->getLatitude() || null === $event->getLocation()->getLongitude()) {
                echo 'KEINE LOCATION DATA!';
                continue;
            }

            $weatherData = $this->requestWeatherData(
                $this->generateApiRequestUrl(
                    $event->getLocation()->getLatitude(),
                    $event->getLocation()->getLongitude(),
                    $event->getStartDate()
                )
            );

            if (null === $event->getWeatherData()) {
                $weatherDataEntity = new WeatherData();
                $weatherDataEntity->setCalendarEvent($event);
                $event->setWeatherData($weatherDataEntity);
                $this->entityManager->persist($weatherDataEntity);
            } else {
                $weatherDataEntity = $event->getWeatherData();
            }
            $weatherDataEntity->setDailyWeatherData($weatherData['daily'] ?? []);
            $weatherDataEntity->setHourlyWeatherData($weatherData['hourly'] ?? []);

            $this->entityManager->persist($weatherDataEntity);
            $this->entityManager->flush();

            sleep(1); // To avoid hitting rate limits
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private function requestWeatherData(string $apiRequestUrl): array
    {
        $client = new Client();
        $response = $client->get($apiRequestUrl);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function generateApiRequestUrl(float $latitude, float $longitude, DateTimeInterface $date): string
    {
        $dateString = $date->format('Y-m-d');

        $params = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hourly' => implode(',', $this->hourlySettings),
            'daily' => implode(',', $this->dailySettings),
            'timezone' => 'auto',
            'start_date' => $dateString,
            'end_date' => $dateString,
        ];

        return $this->weatherApiUrl . '?' . http_build_query($params);
    }
}
