
import React from 'react';
import Fab from '@mui/material/Fab';
import AddIcon from '@mui/icons-material/Add';
import { Tooltip } from '@mui/material';

const CalendarFab: React.FC<{ onClick?: () => void }> = ({ onClick }) => {
  return (
    <Tooltip title="Neues Ereignis" placement="top">
      <Fab color="primary" aria-label="add event" onClick={onClick}>
        <AddIcon />
      </Fab>
    </Tooltip>
  );
};

export default CalendarFab;
