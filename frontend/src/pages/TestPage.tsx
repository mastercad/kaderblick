import React from 'react';
import { Box, Typography } from '@mui/material';
import { TestNotification } from '../components/TestNotification';

export default function TestPage() {
  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h4" gutterBottom>
        Notification System Test
      </Typography>
      <TestNotification />
    </Box>
  );
}
