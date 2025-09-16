import SportsSoccerIcon from '@mui/icons-material/SportsSoccer';
import SquareIcon from '@mui/icons-material/Square';
import ChevronRightOutlined from '@mui/icons-material/ChevronRightOutlined';
import ChevronLeftOutlinedIcon from '@mui/icons-material/ChevronLeftOutlined';
import React from 'react';

export const GAME_EVENT_ICON_MAP: Record<string, React.ReactNode> = {
  'fas fa-futbol': <SportsSoccerIcon sx={{ verticalAlign: 'middle' }} />,
  'fas fa-square': <SquareIcon sx={{ verticalAlign: 'middle' }} />,
  'fas fa-arrow-right': <ChevronRightOutlined sx={{ verticalAlign: 'middle' }} />,
  'fas fa-arrow-left': <ChevronLeftOutlinedIcon sx={{ verticalAlign: 'middle' }} />,
};

// Hilfsfunktion für dynamischen Zugriff
export function getGameEventIconByCode(code: string) {
    return GAME_EVENT_ICON_MAP[code] ?? null;
}
