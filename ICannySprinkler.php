<?php
namespace ulrischa;

interface ICannySprinkler
{
    public function getLatLong(): array;

    public function getBarrelVolume(): ?float;

    public function getPumpOutput(): ?float;

    public function getSoilMoisture(): ?float;

    public function rainsToday($weather_array): bool;

    public function rainsTomorrow($weather_array): bool;

    public function rainedYesterday($weather_array): bool;

    public function daysToNextRain($weather_array): int;

    public function sprinkleNow(): bool;

    public function getSprinkleTime(): float;
}
