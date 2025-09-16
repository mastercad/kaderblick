import React, { useState } from 'react';
import { Box } from '@mui/material';
import FeedbackFab from './FeedbackFab';
import { useFabStack } from './FabStackProvider';
import { useAuth } from '../context/AuthContext';

const FabStack: React.FC = () => {
  const fabStack = useFabStack();
  const { user } = useAuth();

  return (
    <Box
      sx={{
        position: 'fixed',
        bottom: { xs: 16, sm: 24 },
        right: { xs: 16, sm: 24 },
        zIndex: 2000,
        display: 'flex',
        flexDirection: 'row',
        gap: 2,
        alignItems: 'center',
      }}
    >
      {user && <FeedbackFab />}
      {fabStack?.fabs.map(fab => (
        <React.Fragment key={fab.key}>{fab.node}</React.Fragment>
      ))}
    </Box>
  );
};

export default FabStack;