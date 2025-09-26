<?php

namespace App\Entity;

use App\Repository\WeatherRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: WeatherRepository::class)]
#[ORM\Table(
    name: 'weather_data'
)]
class WeatherData
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: CalendarEvent::class, inversedBy: 'weatherData')]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CalendarEvent $calendarEvent;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['weather_data:read'])]
    private array $dailyWeatherData = [];

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['weather_data:read'])]
    private array $hourlyWeatherData = [];

    public function getCalendarEvent(): ?CalendarEvent
    {
        return $this->calendarEvent;
    }

    public function setCalendarEvent(?CalendarEvent $calendarEvent): self
    {
        $this->calendarEvent = $calendarEvent;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDailyWeatherData(): array
    {
        return $this->dailyWeatherData;
    }

    /**
     * @param array<string, mixed> $daily
     */
    public function setDailyWeatherData(array $daily): self
    {
        $this->dailyWeatherData = $daily;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHourlyWeatherData(): array
    {
        return $this->hourlyWeatherData;
    }

    /**
     * @param array<string, mixed> $hourly
     */
    public function setHourlyWeatherData(array $hourly): self
    {
        $this->hourlyWeatherData = $hourly;

        return $this;
    }
}
