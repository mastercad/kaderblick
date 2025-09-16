import React, { useState } from 'react';
import {
  Badge,
  IconButton,
  Popover,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
  Typography,
  Box,
  Button,
  Divider,
  useTheme,
  alpha
} from '@mui/material';
import NotificationsIcon from '@mui/icons-material/Notifications';
import NotificationsNoneIcon from '@mui/icons-material/NotificationsNone';
import NewsIcon from '@mui/icons-material/Article';
import MessageIcon from '@mui/icons-material/Message';
import EventIcon from '@mui/icons-material/Event';
import SystemIcon from '@mui/icons-material/Info';
import DeleteIcon from '@mui/icons-material/Delete';
import MarkAsReadIcon from '@mui/icons-material/DoneAll';
import { useNotifications } from '../context/NotificationContext';
import { AppNotification } from '../types/notifications';

const getNotificationIcon = (type: AppNotification['type']) => {
  switch (type) {
    case 'news': return <NewsIcon fontSize="small" />;
    case 'message': return <MessageIcon fontSize="small" />;
    case 'participation': return <EventIcon fontSize="small" />;
    case 'system': return <SystemIcon fontSize="small" />;
    default: return <SystemIcon fontSize="small" />;
  }
};

export const NotificationCenter: React.FC = () => {
  const theme = useTheme();
  const { notifications, markAsRead, markAllAsRead, clearAll, unreadCount } = useNotifications();
  const [anchorEl, setAnchorEl] = useState<HTMLButtonElement | null>(null);

  const handleClick = (event: React.MouseEvent<HTMLButtonElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleNotificationClick = (notification: AppNotification) => {
    if (!notification.read) {
      markAsRead(notification.id);
    }
    // Optional: Navigation zu relevanter Seite basierend auf notification.data
  };

  const open = Boolean(anchorEl);

  return (
    <>
      <IconButton
        color="inherit"
        onClick={handleClick}
        sx={{ 
          mr: 1,
          '&:hover': {
            backgroundColor: alpha(theme.palette.common.white, 0.1)
          }
        }}
      >
        <Badge badgeContent={unreadCount} color="error" max={99}>
          {unreadCount > 0 ? <NotificationsIcon /> : <NotificationsNoneIcon />}
        </Badge>
      </IconButton>

      <Popover
        open={open}
        anchorEl={anchorEl}
        onClose={handleClose}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'right',
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'right',
        }}
        PaperProps={{
          sx: {
            width: 400,
            maxHeight: 500,
            overflow: 'hidden',
            display: 'flex',
            flexDirection: 'column'
          }
        }}
      >
        <Box sx={{ p: 2, borderBottom: 1, borderColor: 'divider' }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <Typography variant="h6">
              Benachrichtigungen {unreadCount > 0 && `(${unreadCount})`}
            </Typography>
            <Box>
              {unreadCount > 0 && (
                <IconButton size="small" onClick={markAllAsRead} title="Alle als gelesen markieren">
                  <MarkAsReadIcon fontSize="small" />
                </IconButton>
              )}
              {notifications.length > 0 && (
                <IconButton size="small" onClick={clearAll} title="Alle löschen">
                  <DeleteIcon fontSize="small" />
                </IconButton>
              )}
            </Box>
          </Box>
        </Box>

        <Box sx={{ flex: 1, overflow: 'auto' }}>
          {notifications.length === 0 ? (
            <Box sx={{ p: 3, textAlign: 'center' }}>
              <NotificationsNoneIcon sx={{ fontSize: 48, color: 'text.secondary', mb: 1 }} />
              <Typography variant="body2" color="text.secondary">
                Keine Benachrichtigungen
              </Typography>
            </Box>
          ) : (
            <List sx={{ p: 0 }}>
              {notifications.map((notification, index) => (
                <React.Fragment key={notification.id}>
                  <ListItem
                    component="div"
                    onClick={() => handleNotificationClick(notification)}
                    sx={{
                      backgroundColor: notification.read ? 'transparent' : alpha(theme.palette.primary.main, 0.05),
                      '&:hover': {
                        backgroundColor: alpha(theme.palette.primary.main, 0.1)
                      },
                      py: 1.5,
                      cursor: 'pointer'
                    }}
                  >
                    <ListItemIcon sx={{ minWidth: 40 }}>
                      {getNotificationIcon(notification.type)}
                    </ListItemIcon>
                    <ListItemText
                      primary={notification.title}
                      secondary={
                        <Box component="span">
                          <Box 
                            component="span"
                            sx={{
                              display: '-webkit-box',
                              color: 'text.secondary',
                              fontSize: '0.875rem',
                              lineHeight: 1.43,
                              letterSpacing: '0.01071em',
                              mb: 0.5,
                              overflow: 'hidden',
                              textOverflow: 'ellipsis',
                              WebkitLineClamp: 2,
                              WebkitBoxOrient: 'vertical'
                            }}
                          >
                            {notification.message}
                          </Box>
                          <Box 
                            component="span"
                            sx={{
                              display: 'block',
                              color: 'text.secondary',
                              fontSize: '0.75rem',
                              lineHeight: 1.66,
                              letterSpacing: '0.03333em'
                            }}
                          >
                            {notification.timestamp instanceof Date 
                              ? notification.timestamp.toLocaleString('de-DE', {
                                  day: '2-digit',
                                  month: '2-digit',
                                  hour: '2-digit',
                                  minute: '2-digit'
                                })
                              : 'Ungültiges Datum'
                            }
                          </Box>
                        </Box>
                      }
                      primaryTypographyProps={{
                        sx: { 
                          fontWeight: notification.read ? 'normal' : 'bold',
                          display: '-webkit-box',
                          WebkitLineClamp: 1,
                          WebkitBoxOrient: 'vertical',
                          overflow: 'hidden'
                        }
                      }}
                    />
                  </ListItem>
                  {index < notifications.length - 1 && <Divider />}
                </React.Fragment>
              ))}
            </List>
          )}
        </Box>
      </Popover>
    </>
  );
};
