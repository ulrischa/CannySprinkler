<?php
namespace ulrischa;

include_once('ICannySprinkler.php');

class OpenWeatherSprinkler implements ICannySprinkler
{
    private $apiKey;
    public $latLong;
    public $barrelVolume;
    public $pumpOutput;
    public $soilMoisture;
    public $params = array('soil_moisture_lower'=>30, 'soil_moisture_upper'=>80,'barrel_buffer' => 0.5);
    
    private $contentsNowForcast;
    private $contentsHisto;

    public function __construct($apiKey, $latLong, $barrelVolume = null, $pumpOutput = null, $soilMoisture = null)
    {
        $this->apiKey = $apiKey;
        $this->latLong = $latLong;
        $this->barrelVolume = $barrelVolume;
        $this->pumpOutput = $pumpOutput;
        $this->soilMoisture = $soilMoisture;
        $this->setContentsNowForcast();
        $this->setContentsHisto();
    }

    protected function urlNowForcast()
    {
        return "http://api.openweathermap.org/data/2.5/onecall?lat=" . $this->latLong['lat'] . "&lon=" . $this->latLong['lon'] . "&exclude=minutely,hourly,alert&appid=" . $this->apiKey;
    }

    protected function urlHisto()
    {
        return "https://api.openweathermap.org/data/2.5/onecall/timemachine?lat=" . $this->latLong['lat'] . "&lon=" . $this->latLong['lon'] . "&dt=" . strtotime("-1 day") . "&appid=" . $this->apiKey;
    }

    protected function setContentsNowForcast()
    {
        $contents_now_forcast = file_get_contents($this->urlNowForcast());
        $this->contentsNowForcast = json_decode($contents_now_forcast, true);
    }

    protected function setContentsHisto()
    {
        $contents_histo = file_get_contents($this->urlHisto());
        $this->contentsHisto = json_decode($contents_histo, true);
    }
    
    public function getContentsNowForcast()
    {
        if (empty($this->contentsNowForcast)) {
            $this->setContentsNowForcast();
        }
        return $this->contentsNowForcast;
    }

    public function getContentsHisto()
    {
        if (empty($this->contentsHisto)) {
            $this->setContentsHisto();
        }
        return $this->contentsHisto;
    }
    
    public function getLatLong(): array
    {
        return $this->latLong;
    }

    public function getBarrelVolume(): ?float
    {
        return $this->barrelVolume;
    }

    public function getPumpOutput(): ?float
    {
        return $this->pumpOutput;
    }

    public function getSoilMoisture(): ?float
    {
        return $this->soilMoisture;
    }
    
    public function rainsToday($weather_arr): bool
    {
        if (empty($weather_arr)) $weather_arr = $this->getContentsNowForcast();
        if (!empty($weather_arr['current']['rain'])) {
            return true;
        }
        return false;
    }

    public function rainsTomorrow($weather_arr): bool
    {
        if (empty($weather_arr)) $weather_arr = $this->getContentsNowForcast();
        if (!empty($weather_arr['daily'][0]['rain'])) {
            return true;
        }
        return false;
    }

    public function rainedYesterday($weather_arr): bool
    {
        if (empty($weather_arr)) $weather_arr = $this->getContentsHisto();
        $hours_with_rain = 0;
        foreach ($weather_arr['hourly'] as $h) {
            if (!empty($h['rain'])) {
                $hours_with_rain = $hours_with_rain + 1;
            }
        }
        if ($hours_with_rain > 1) {
            return true;
        }
        return false;
    }
    
    public function sprinkleNow(): bool
    {
        if ($this->rainsToday(null) === true) {
            return false;
        }
        if ($this->getSoilMoisture() !== null && $this->getSoilMoisture() <= $this->params['soil_moisture_lower']) {
            return true;
        }
        if ($this->getSoilMoisture() !== null && $this->getSoilMoisture() >= $this->params['soil_moisture_upper']) {
            return false;
        }
        if ($this->rainedYesterday(null) === true) {
            return false;
        }
        if ($this->rainsTomorrow(null) === true) {
            return false;
        }
        return true;
    }
    
    public function daysToNextRain($weather_arr): int
    {
        if (empty($weather_arr)) $weather_arr = $this->getContentsNowForcast();
        foreach ($weather_arr['daily'] as $idx => $d) {
            if (!empty($d['rain'])) {
                return $idx + 1;
            }
        }
        return 7;
    }

    public function getSprinkleTime(): float
    {
        $sprinkle_seconds = (($this->getBarrelVolume()*$this->params['barrel_buffer'] /$this->daysToNextRain(null))/ $this->getPumpOutput())*3600;
        return $sprinkle_seconds;
    }
}


