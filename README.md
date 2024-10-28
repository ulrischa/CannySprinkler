!Openweather hast discontinued the free 2.5 api. So this will not work.

# CannySprinkler
Check if sprinkling lawn is necessary.

Checks, if it rains today, rained yesterday or will rain tomorrow for a given latitude and longitude and tells you if your lawn should be sprinkled.
If you have values for soil moisture you can add them too.
You can also get the approx. sprinkle time.
The algorithm is designed for most economical pumping. Supposing you use something like this: https://www.gardena.com/de/produkte/bewasserung/pumpen/regenfasspumpe-4000-1/967974701/

You neeed a API Key for OpenWeatherSprinkler https://home.openweathermap.org/users/sign_up

## Usage:
Example:

52.463, 13.469: Latitude and Longitude for Olching, Germany

300 l Barrel Volume

4000 l/h Pump Output

Null Soil Moisture

$sprinkler = new ulrischa\OpenWeatherSprinkler('OpenWeatherAPIKey', array('lat' => 52.463, 'lon' => 13.469), 300, 4000, null);

var_dump($sprinkler->sprinkleNow());

var_dump($sprinkler->getSprinkleTime());

