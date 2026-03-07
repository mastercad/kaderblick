import React from 'react';
import { Box, Tooltip } from '@mui/material';
import { getZoneColor, truncateName } from '../helpers';
import type { PlayerData } from '../types';

interface PlayerTokenProps {
  player: PlayerData;
  isDragging: boolean;
  onMouseDown: (e: React.MouseEvent) => void;
  onTouchStart: (e: React.TouchEvent) => void;
}

/**
 * A player token displayed on the pitch.
 * Shows the shirt number in a color-coded circle (by field zone)
 * and the player's name in a label beneath it.
 */
const PlayerToken: React.FC<PlayerTokenProps> = ({ player, isDragging, onMouseDown, onTouchStart }) => (
  <Tooltip title={player.name} placement="top" disableInteractive>
    <Box
      sx={{
        position: 'absolute',
        left: `${player.x}%`,
        top: `${player.y}%`,
        transform: 'translate(-50%, -50%)',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        cursor: isDragging ? 'grabbing' : 'grab',
        zIndex: isDragging ? 100 : 1,
        touchAction: 'none',
        userSelect: 'none',
      }}
      onMouseDown={onMouseDown}
      onTouchStart={onTouchStart}
    >
      {/* Numbered circle */}
      <Box sx={{
        width: 44,
        height: 44,
          bgcolor: getZoneColor(player.y),
        color: 'white',
        borderRadius: '50%',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontWeight: 800,
        fontSize: 15,
        border: '2px solid rgba(255,255,255,0.85)',
        boxShadow: '0 2px 8px rgba(0,0,0,0.55)',
        flexShrink: 0,
      }}>
        {player.number}
      </Box>

      {/* Name label */}
      <Box sx={{
        mt: '2px',
        bgcolor: 'rgba(0,0,0,0.68)',
        color: 'white',
        borderRadius: '4px',
        px: '4px',
        lineHeight: '1.4',
        fontSize: '0.62rem',
        fontWeight: 600,
        maxWidth: 58,
        textAlign: 'center',
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
      }}>
        {truncateName(player.name)}
      </Box>
    </Box>
  </Tooltip>
);

export default PlayerToken;
