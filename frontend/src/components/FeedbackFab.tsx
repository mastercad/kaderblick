import React, { useState } from 'react';
import { useLocation } from 'react-router-dom';
import { useTheme } from '@mui/material/styles';
import Fab from '@mui/material/Fab';
import Tooltip from '@mui/material/Tooltip';
import FeedbackIcon from '@mui/icons-material/Feedback';
import FeedbackModal from '../modals/FeedbackModal';

const FeedbackFab: React.FC = () => {
  const [open, setOpen] = useState(false);
  const theme = useTheme();
  const location = useLocation();
  const isHome = location.pathname === '/' || location.pathname === '';

  return (
    <>
      <Tooltip title="Feedback geben" placement="left">
        <Fab color='primary'
          aria-label="Feedback geben" onClick={() => setOpen(true)}>
          <FeedbackIcon sx={{ color: isHome ? '#fff' : 'primary' }} />
        </Fab>
      </Tooltip>
      <FeedbackModal open={open} onClose={() => setOpen(false)} />
    </>
  );
};

export default FeedbackFab;
