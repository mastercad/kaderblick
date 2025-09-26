import React from 'react';
import {
  WiDaySunny,
  WiDaySunnyOvercast,
  WiCloud,
  WiCloudy,
  WiFog,
  WiSprinkle,
  WiRain,
  WiRainMix,
  WiSnow,
  WiSleet,
  WiShowers,
  WiSnowWind,
  WiThunderstorm
} from 'react-icons/wi';

// Typ für Theme
type Theme = 'light' | 'dark';

// Typ für Wetter-Icon-Eintrag
interface WeatherEntry {
  icon: React.ReactElement;
  description: string;
}

// Funktion, um Farbe abhängig vom Code und Theme zu liefern
const getColor = (code: number, theme: Theme = 'light'): string => {
  const isDark = theme === 'dark';
  switch(code){
    case 0: case 1: case 2: case 3: return isDark ? '#FFD700' : '#FFA500'; // Sonne
    case 45: case 48: return isDark ? '#CCCCCC' : '#888888'; // Nebel
    case 51: case 53: case 55: case 56: case 57: case 61: case 63: case 65: case 66: case 67:
      return isDark ? '#74b9ff' : '#0984e3'; // Regen
    case 71: case 73: case 75: case 77: case 85: case 86:
      return isDark ? '#dfe6e9' : '#ffffff'; // Schnee/Graupel
    case 80: case 81: case 82: return isDark ? '#74b9ff' : '#0984e3'; // Regenschauer
    case 95: case 96: case 99: return isDark ? '#e17055' : '#d63031'; // Gewitter
    default: return isDark ? '#ffffff' : '#333333'; // Standard
  }
};

// Wetter-Map Funktion
export const weatherIcons = (theme: Theme = 'light'): Record<number, WeatherEntry> => ({
  0: { icon: <WiDaySunny size={48} color={getColor(0, theme)} />, description: "Klarer Himmel" },
  1: { icon: <WiDaySunnyOvercast size={48} color={getColor(1, theme)} />, description: "Wenige Wolken" },
  2: { icon: <WiCloud size={48} color={getColor(2, theme)} />, description: "Teilweise bewölkt" },
  3: { icon: <WiCloudy size={48} color={getColor(3, theme)} />, description: "Bewölkt" },
  45: { icon: <WiFog size={48} color={getColor(45, theme)} />, description: "Nebel" },
  48: { icon: <WiFog size={48} color={getColor(48, theme)} />, description: "Eisnebel" },
  51: { icon: <WiSprinkle size={48} color={getColor(51, theme)} />, description: "Leichter Nieselregen" },
  53: { icon: <WiSprinkle size={48} color={getColor(53, theme)} />, description: "Mäßiger Nieselregen" },
  55: { icon: <WiSprinkle size={48} color={getColor(55, theme)} />, description: "Starker Nieselregen" },
  56: { icon: <WiRainMix size={48} color={getColor(56, theme)} />, description: "Leichter gefrierender Nieselregen" },
  57: { icon: <WiRainMix size={48} color={getColor(57, theme)} />, description: "Starker gefrierender Nieselregen" },
  61: { icon: <WiRain size={48} color={getColor(61, theme)} />, description: "Leichter Regen" },
  63: { icon: <WiRain size={48} color={getColor(63, theme)} />, description: "Mäßiger Regen" },
  65: { icon: <WiRain size={48} color={getColor(65, theme)} />, description: "Starker Regen" },
  66: { icon: <WiRainMix size={48} color={getColor(66, theme)} />, description: "Leichter gefrierender Regen" },
  67: { icon: <WiRainMix size={48} color={getColor(67, theme)} />, description: "Starker gefrierender Regen" },
  71: { icon: <WiSnow size={48} color={getColor(71, theme)} />, description: "Leichter Schneefall" },
  73: { icon: <WiSnow size={48} color={getColor(73, theme)} />, description: "Mäßiger Schneefall" },
  75: { icon: <WiSnow size={48} color={getColor(75, theme)} />, description: "Starker Schneefall" },
  77: { icon: <WiSleet size={48} color={getColor(77, theme)} />, description: "Graupel" },
  80: { icon: <WiShowers size={48} color={getColor(80, theme)} />, description: "Leichter Regenschauer" },
  81: { icon: <WiShowers size={48} color={getColor(81, theme)} />, description: "Mäßiger Regenschauer" },
  82: { icon: <WiShowers size={48} color={getColor(82, theme)} />, description: "Starker Regenschauer" },
  85: { icon: <WiSnowWind size={48} color={getColor(85, theme)} />, description: "Leichter Schneeschauer" },
  86: { icon: <WiSnowWind size={48} color={getColor(86, theme)} />, description: "Starker Schneeschauer" },
  95: { icon: <WiThunderstorm size={48} color={getColor(95, theme)} />, description: "Gewitter" },
  96: { icon: <WiThunderstorm size={48} color={getColor(96, theme)} />, description: "Gewitter mit leichtem Hagel" },
  99: { icon: <WiThunderstorm size={48} color={getColor(99, theme)} />, description: "Gewitter mit starkem Hagel" },
});

// Beispiel-Komponente für Anzeige
interface WeatherDisplayProps {
  code: number;
  theme?: Theme;
}

export const WeatherDisplay: React.FC<WeatherDisplayProps> = ({ code, theme = 'light' }) => {
  const weatherMap = weatherIcons(theme);
  const weather = weatherMap[code] || { icon: <WiCloudy size={48} />, description: "Unbekanntes Wetter" };

  return (
    <div title={`${weather.description}`}>
      {weather.icon}
    </div>
  );
};
