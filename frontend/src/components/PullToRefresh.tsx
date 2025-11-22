import { Box, CircularProgress } from '@mui/material';
import { usePullToRefresh } from '../hooks/usePullToRefresh';
import { ReactNode } from 'react';
import RefreshIcon from '@mui/icons-material/Refresh';

interface PullToRefreshProps {
  onRefresh: () => Promise<void>;
  children: ReactNode;
  isEnabled?: boolean;
}

export const PullToRefresh = ({ onRefresh, children, isEnabled = true }: PullToRefreshProps) => {
  const { isPulling, pullDistance, isRefreshing } = usePullToRefresh({
    onRefresh,
    threshold: 80,
    maxPullDistance: 150,
    isEnabled,
  });

  const rotation = (pullDistance / 150) * 360;
  const opacity = Math.min(pullDistance / 80, 1);

  return (
    <Box sx={{ position: 'relative', width: '100%', minHeight: '100vh' }}>
      {/* Pull-to-Refresh Indikator */}
      <Box
        sx={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          height: pullDistance,
          display: 'flex',
          alignItems: 'flex-end',
          justifyContent: 'center',
          pb: 2,
          zIndex: 9999,
          pointerEvents: 'none',
          transition: isRefreshing ? 'height 0.2s ease' : 'none',
        }}
      >
        {isPulling && (
          <Box
            sx={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              opacity: opacity,
              transform: `scale(${Math.min(opacity, 1)})`,
              transition: 'opacity 0.2s ease, transform 0.2s ease',
            }}
          >
            {isRefreshing ? (
              <CircularProgress size={32} />
            ) : (
              <RefreshIcon
                sx={{
                  fontSize: 32,
                  color: 'primary.main',
                  transform: `rotate(${rotation}deg)`,
                  transition: 'transform 0.1s ease',
                }}
              />
            )}
          </Box>
        )}
      </Box>

      {/* Content */}
      <Box
        sx={{
          transform: `translateY(${isPulling ? pullDistance : 0}px)`,
          transition: isPulling && !isRefreshing ? 'none' : 'transform 0.2s ease',
        }}
      >
        {children}
      </Box>
    </Box>
  );
};
