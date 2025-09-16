import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import List from '@mui/material/List';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import ListItemButton from '@mui/material/ListItemButton';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import BarChartIcon from '@mui/icons-material/BarChart';
import EmailIcon from '@mui/icons-material/Email';
import NewspaperIcon from '@mui/icons-material/Newspaper';
import EventIcon from '@mui/icons-material/Event';
import DescriptionIcon from '@mui/icons-material/Description';

interface AddWidgetModalProps {
  open: boolean;
  onClose: () => void;
  onAdd: (widgetType: string) => void;
  onReportWidgetFlow: () => void;
}

const widgetOptions = [
  { type: 'calendar', label: 'Kalender', icon: <CalendarMonthIcon /> },
  { type: 'stats', label: 'Statistiken', icon: <BarChartIcon /> },
  { type: 'messages', label: 'Nachrichten', icon: <EmailIcon /> },
  { type: 'news', label: 'News', icon: <NewspaperIcon /> },
  { type: 'upcoming_events', label: 'Anstehende Veranstaltungen', icon: <EventIcon /> },
  { type: 'report', label: 'Report Widget', icon: <DescriptionIcon /> },
];

export const AddWidgetModal: React.FC<AddWidgetModalProps> = ({
  open,
  onClose,
  onAdd,
  onReportWidgetFlow,
}) => (
  <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
    <DialogTitle>Widget hinzufügen</DialogTitle>
    <DialogContent>
      <List>
        {widgetOptions.map(opt => (
          <ListItemButton
            key={opt.type}
            onClick={
              opt.type === 'report'
                ? onReportWidgetFlow
                : () => onAdd(opt.type)
            }
          >
            <ListItemIcon>{opt.icon}</ListItemIcon>
            <ListItemText primary={opt.label} />
          </ListItemButton>
        ))}
      </List>
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose} color="secondary">Abbrechen</Button>
    </DialogActions>
  </Dialog>
);
