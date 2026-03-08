import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Chip from '@mui/material/Chip';
import Stack from '@mui/material/Stack';
import Divider from '@mui/material/Divider';
import Paper from '@mui/material/Paper';
import Avatar from '@mui/material/Avatar';
import Tooltip from '@mui/material/Tooltip';
import Collapse from '@mui/material/Collapse';
import React, { useState, useEffect } from 'react';
import CircularProgress from '@mui/material/CircularProgress';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Alert from '@mui/material/Alert';
import useMediaQuery from '@mui/material/useMediaQuery';
import { useTheme } from '@mui/material/styles';
import CancelIcon from '@mui/icons-material/EventBusy';
import RestoreIcon from '@mui/icons-material/EventAvailable';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import DescriptionIcon from '@mui/icons-material/Description';
import GroupIcon from '@mui/icons-material/Group';
import SportsSoccerIcon from '@mui/icons-material/SportsSoccer';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import ExpandLessIcon from '@mui/icons-material/ExpandLess';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import ChatBubbleOutlineIcon from '@mui/icons-material/ChatBubbleOutline';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';
import { apiJson, apiRequest } from '../utils/api';
import { WeatherDisplay } from '../components/WeatherIcons';
import WeatherModal from './WeatherModal';
import Location from '../components/Location';
import { FaCar } from 'react-icons/fa';
import TeamRideDetailsModal from './TeamRideDetailsModal';
import TourTooltip from '../components/TourTooltip';
import BaseModal from './BaseModal';

export interface EventDetailsModalProps {
  open: boolean;
  onClose: () => void;
  event: {
    id: number;
    title: string;
    start: Date | string;
    end: Date | string;
    description?: string;
    type?: { name?: string; color?: string };
    location?: { 
      name?: string,
      latitude?: number,
      longitude?: number,
      city?: string,
      address?: string
    };
    weatherData?: { weatherCode?: number };
    game?: {
      homeTeam?: { name: string };
      awayTeam?: { name: string };
      gameType?: { name: string };
    };
    task?: {
      id: number;
      isRecurring: boolean;
      recurrenceMode: string;
      recurrenceRule: string | null;
      rotationUsers: { id: number; fullName: string }[];
      rotationCount: number;
    };
    permissions?: {
      canEdit?: boolean;
      canDelete?: boolean;
      canCancel?: boolean;
      canViewRides?: boolean;
      canParticipate?: boolean;
    };
    cancelled?: boolean;
    cancelReason?: string;
    cancelledBy?: string;
  } | null;

  onEdit?: () => void;
  showEdit?: boolean;
  onDelete?: () => void;
  onCancelled?: () => void;

  // Teilnahme-API Props
  participationStatuses?: Array<{
    id: number;
    name: string;
    color?: string;
    icon?: string;
  }>;

  currentParticipation?: {
    statusId: number;
    statusName: string;
    color?: string;
    icon?: string;
    note?: string;
  };

  participations?: Array<{
    available_statuses : Array<{
      id: number;
      name: string;
      color?: string;
      icon?: string;
      code: string,
      sort_order: number
    }>,
    event: {
      id: number;
      is_game: boolean;
      title: string;
      type: string;
      weatherData?: { weatherCode?: number };
    },
    participations: Array<{
      user_id: number;
      user_name: string;
      is_team_player: boolean;
      note?: string;
      status: {
        id: number;
        name: string;
        color?: string;
        icon?: string;
        code: string;
      };
    }>
  }>;
  onParticipationChange?: (statusId: number, note: string) => void;
  loadingParticipation?: boolean;
  initialOpenRides?: boolean;
}

export const EventDetailsModal: React.FC<EventDetailsModalProps> = ({
  open,
  onClose,
  event,
  onEdit,
  showEdit = false,
  onDelete,
  onCancelled,
  initialOpenRides = false,
}) => {
  const [participationStatuses, setParticipationStatuses] = useState<Array<any>>([]);
  const [currentParticipation, setCurrentParticipation] = useState<any>(null);
  const [participations, setParticipations] = useState<Array<any>>([]);
  // Note dialog state
  const [noteDialogOpen, setNoteDialogOpen] = useState(false);
  const [pendingStatusId, setPendingStatusId] = useState<number | null>(null);
  const [dialogNote, setDialogNote] = useState('');
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [weatherModalOpen, setWeatherModalOpen] = useState(false);
  const [selectedEventId, setSelectedEventId] = useState<number | null>(null);
  const [teamRideModalOpen, setTeamRideModalOpen] = useState(false);
  const [teamRideStatus, setTeamRideStatus] = useState<'none'|'full'|'free'>('none');
  const [cancelDialogOpen, setCancelDialogOpen] = useState(false);
  const [cancelReason, setCancelReason] = useState('');
  const [cancelling, setCancelling] = useState(false);
  const [reactivating, setReactivating] = useState(false);

  const handleReactivateEvent = async () => {
    if (!event) return;
    setReactivating(true);
    try {
      const res = await apiRequest(`/api/calendar/event/${event.id}/reactivate`, {
        method: 'PATCH',
      });
      if (res.ok) {
        onCancelled?.();
        onClose();
      } else {
        const data = await res.json().catch(() => ({}));
        alert(data.error || 'Fehler beim Reaktivieren.');
      }
    } catch {
      alert('Fehler beim Reaktivieren.');
    } finally {
      setReactivating(false);
    }
  };

  const handleCancelEvent = async () => {
    if (!event || !cancelReason.trim()) return;
    setCancelling(true);
    try {
      const res = await apiRequest(`/api/calendar/event/${event.id}/cancel`, {
        method: 'PATCH',
        body: { reason: cancelReason.trim() },
      });
      if (res.ok) {
        setCancelDialogOpen(false);
        setCancelReason('');
        onCancelled?.();
        onClose();
      } else {
        const data = await res.json().catch(() => ({}));
        alert(data.error || 'Fehler beim Absagen.');
      }
    } catch {
      alert('Fehler beim Absagen.');
    } finally {
      setCancelling(false);
    }
  };

  // Auto-open rides modal when initialOpenRides is set
  useEffect(() => {
    if (initialOpenRides && open && event?.id && event?.permissions?.canViewRides) {
      setSelectedEventId(event.id);
      setTeamRideModalOpen(true);
    }
  }, [initialOpenRides, open, event?.id, event?.permissions?.canViewRides]);

  const tourSteps = [
    {
      target: "event-details",
      content: "Hier findest du alle wichtigen Infos zum Event. Schau dich ruhig um!",
    },
    {
      target: "event-action-button",
      content: "Hier kannst du dich zum Event anmelden oder abmelden. Nach dem Klick öffnet sich ein Fenster, wo du optional eine Nachricht hinterlassen kannst.",
    },
    /*
    {
      target: "current-participation-status",
      content: "Hier siehst du deinen aktuellen Anmeldestatus für dieses Event.",
    },
    */
    {
      target: "participations-list",
      content: "In der Teilnehmerliste siehst du, wer sich sonst noch angemeldet hat.",
    },
    {
      target: "weather-information",
      content: "Hier findest du Informationen zum vorraussichtlichen Wetter, dabei werden Wetterinformationen nur über die Zeit des Events angezeigt.",
    },
    {
      target: "teamride-information",
      content: "Hier findest du Informationen zu Fahrgemeinschaften, kannst eine Fahrgemeinschaft anbieten bzw. einen Platz buchen.",
    },
  ];

  useEffect(() => {
    if (!event?.id || !open || !event?.permissions?.canViewRides) {
      setTeamRideStatus('none');
      return;
    }
    apiJson(`/api/teamrides/event/${event.id}`)
      .then(data => {
        const rides = data.rides || [];
        if (rides.length === 0) {
          setTeamRideStatus('none');
        } else if (rides.every((r: any) => r.availableSeats === 0)) {
          setTeamRideStatus('full');
        } else {
          setTeamRideStatus('free');
        }
      })
      .catch(() => setTeamRideStatus('none'));
  }, [event?.id, open]);
  
  const openWeatherModal = (eventId: number | null) => {
    setSelectedEventId(eventId);
    setWeatherModalOpen(true);
  };

  const openTeamRideDetails = (eventId: number | null) => {
    setSelectedEventId(eventId);
    setTeamRideModalOpen(true);
  };

  useEffect(() => {
    if (!event || !open || !event.permissions?.canParticipate) {
      setParticipationStatuses([]);
      setCurrentParticipation(null);
      setParticipations([]);
      return;
    }
    setLoading(true);
    Promise.all([
      apiJson(`/api/participation/statuses`),
      apiJson(`/api/participation/event/${event.id}`).catch(() => ({})),
    ])
      .then(([statusesResponse, list]) => {
        if (!Array.isArray(statusesResponse.statuses)) {
          console.error('FEHLER: participation/statuses liefert kein Array!', statusesResponse);
        }
        setParticipationStatuses(Array.isArray(statusesResponse.statuses) ? statusesResponse.statuses : []);
        setParticipations(list.participations ?? []);
        if (list.my_participation) {
          const mp = list.my_participation;
          setCurrentParticipation({
            statusId: mp.status_id,
            statusName: mp.status_name,
            color: mp.status_color,
            icon: mp.status_icon,
            note: mp.note,
          });
        } else {
          setCurrentParticipation(null);
        }
      })
      .catch(() => {
        setParticipationStatuses([]);
        setCurrentParticipation(null);
        setParticipations([]);
      })
      .finally(() => setLoading(false));
  }, [event, open, event?.permissions?.canParticipate]);

  const handleStatusClick = (statusId: number) => {
    if (!event) return;
    setPendingStatusId(statusId);
    // Pre-fill note if user already has a note for this event
    setDialogNote(currentParticipation?.note ?? '');
    setNoteDialogOpen(true);
  };

  const handleParticipationSubmit = () => {
    if (!event || !pendingStatusId) return;
    setNoteDialogOpen(false);
    setSaving(true);
    apiJson(`/api/participation/event/${event.id}/respond`, {
      method: 'POST',
      body: { status_id: pendingStatusId, note: dialogNote },
    })
      .then((response) => {
        if (response.my_participation) {
          const mp = response.my_participation;
          setCurrentParticipation({
            statusId: mp.status_id,
            statusName: mp.status_name,
            color: mp.status_color,
            icon: mp.status_icon,
            note: mp.note,
          });
        }
        return apiJson(`/api/participation/event/${event.id}`);
      })
      .then((list) => {
        setParticipations(list.participations ?? []);
        if (list.my_participation) {
          const mp = list.my_participation;
          setCurrentParticipation({
            statusId: mp.status_id,
            statusName: mp.status_name,
            color: mp.status_color,
            icon: mp.status_icon,
            note: mp.note,
          });
        }
      })
      .finally(() => setSaving(false));
  };

  const groupedParticipations: Record<string, { statusName: string; color?: string; icon?: string; participants: typeof participations }> = {};
  participations.forEach(p => {
    if (!groupedParticipations[p.status.name]) {
      groupedParticipations[p.status.name] = {
        statusName: p.status.name,
        color: p.status.color,
        icon: p.status.icon,
        participants: [],
      };
    }
    groupedParticipations[p.status.name].participants.push(p);
  });

  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const [participantsExpanded, setParticipantsExpanded] = useState(true);

  if (!event) return null;

  // Format date/time helpers
  const startDate = new Date(event.start);
  const endDate = new Date(event.end);
  const isSameDay = startDate.toDateString() === endDate.toDateString();
  const dateStr = startDate.toLocaleDateString('de-DE', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
  const startTimeStr = startDate.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
  const endTimeStr = endDate.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
  const endDateStr = !isSameDay
    ? endDate.toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' })
    : '';

  const typeColor = event.type?.color || theme.palette.primary.main;

  return (
    <>
      <BaseModal
        open={open}
        onClose={onClose}
        title={
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, flexWrap: 'wrap', minWidth: 0 }}>
            {event.type?.name && (
              <Chip
                label={event.type.name}
                size="small"
                sx={{
                  bgcolor: typeColor,
                  color: '#fff',
                  fontWeight: 700,
                  fontSize: '0.7rem',
                  letterSpacing: 0.5,
                  height: 24,
                  textTransform: 'uppercase',
                }}
              />
            )}
            <Typography
              variant="h6"
              component="span"
              sx={{
                fontWeight: 700,
                lineHeight: 1.25,
                textDecoration: event.cancelled ? 'line-through' : 'none',
                opacity: event.cancelled ? 0.6 : 1,
                overflow: 'hidden',
                textOverflow: 'ellipsis',
              }}
            >
              {event.title}
            </Typography>
          </Box>
        }
        maxWidth="sm"
        actions={
          <Stack
            direction="row"
            spacing={1}
            sx={{ width: '100%', justifyContent: 'flex-end', flexWrap: 'wrap', gap: 1 }}
          >
            {event.permissions?.canCancel && !event.cancelled && (
              <Button
                onClick={() => setCancelDialogOpen(true)}
                color="warning"
                variant="outlined"
                size={isMobile ? 'small' : 'medium'}
                startIcon={<CancelIcon />}
                sx={{ borderRadius: 2 }}
              >
                Absagen
              </Button>
            )}
            {event.permissions?.canCancel && event.cancelled && (
              <Button
                onClick={handleReactivateEvent}
                color="success"
                variant="outlined"
                size={isMobile ? 'small' : 'medium'}
                startIcon={<RestoreIcon />}
                disabled={reactivating}
                sx={{ borderRadius: 2 }}
              >
                {reactivating ? 'Wird reaktiviert...' : 'Reaktivieren'}
              </Button>
            )}
            {event.permissions?.canDelete && onDelete && (
              <Button
                onClick={onDelete}
                color="error"
                variant="outlined"
                size={isMobile ? 'small' : 'medium'}
                startIcon={<DeleteIcon />}
                sx={{ borderRadius: 2 }}
              >
                Löschen
              </Button>
            )}
            {event.permissions?.canEdit && onEdit && (
              <Button
                onClick={onEdit}
                variant="outlined"
                size={isMobile ? 'small' : 'medium'}
                startIcon={<EditIcon />}
                sx={{ borderRadius: 2 }}
              >
                Bearbeiten
              </Button>
            )}
            <Button
              onClick={onClose}
              variant="contained"
              size={isMobile ? 'small' : 'medium'}
              sx={{ borderRadius: 2, ml: 'auto' }}
            >
              Schließen
            </Button>
          </Stack>
        }
      >
        {/* Cancelled Banner */}
        {event.cancelled && (
          <Paper
            elevation={0}
            sx={{
              mb: 2,
              p: 1.5,
              borderRadius: 2,
              bgcolor: 'error.main',
              color: '#fff',
              display: 'flex',
              alignItems: 'flex-start',
              gap: 1.5,
            }}
          >
            <CancelIcon sx={{ mt: 0.25, fontSize: 28 }} />
            <Box>
              <Typography variant="subtitle2" fontWeight={700}>Abgesagt</Typography>
              {event.cancelReason && (
                <Typography variant="body2" sx={{ opacity: 0.92 }}>{event.cancelReason}</Typography>
              )}
              {event.cancelledBy && (
                <Typography variant="caption" sx={{ opacity: 0.75 }}>
                  Abgesagt von {event.cancelledBy}
                </Typography>
              )}
              {event.permissions?.canCancel && (
                <Button
                  onClick={handleReactivateEvent}
                  variant="contained"
                  size="small"
                  startIcon={<RestoreIcon />}
                  disabled={reactivating}
                  sx={{
                    mt: 1,
                    borderRadius: 2,
                    bgcolor: 'rgba(255,255,255,0.2)',
                    color: '#fff',
                    '&:hover': { bgcolor: 'rgba(255,255,255,0.35)' },
                  }}
                >
                  {reactivating ? 'Wird reaktiviert...' : 'Event reaktivieren'}
                </Button>
              )}
            </Box>
          </Paper>
        )}

        <Box id="event-details" sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
          {/* --- Date / Time / Quick Actions Row --- */}
          <Paper
            variant="outlined"
            sx={{
              p: 2,
              borderRadius: 2,
              display: 'flex',
              alignItems: 'stretch',
              gap: 2,
              flexDirection: isMobile ? 'column' : 'row',
            }}
          >
            {/* Date & Time */}
            <Box sx={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 0.75 }}>
              <Stack direction="row" spacing={1} alignItems="center">
                <CalendarTodayIcon sx={{ fontSize: 18, color: 'text.secondary' }} />
                <Typography variant="body2" fontWeight={600}>{dateStr}</Typography>
              </Stack>
              <Stack direction="row" spacing={1} alignItems="center">
                <AccessTimeIcon sx={{ fontSize: 18, color: 'text.secondary' }} />
                <Typography variant="body2" color="text.secondary">
                  {startTimeStr} – {isSameDay ? endTimeStr : `${endDateStr} ${endTimeStr}`}
                </Typography>
              </Stack>
            </Box>

            {/* Weather + Rides quick-access icons */}
            <Stack
              direction="row"
              spacing={1.5}
              alignItems="center"
              justifyContent={isMobile ? 'flex-start' : 'flex-end'}
              sx={isMobile ? { pt: 0.5 } : {}}
            >
              <Tooltip title="Wetterdetails" arrow>
                <Box
                  id="weather-information"
                  onClick={() => openWeatherModal(event.id)}
                  sx={{
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    borderRadius: 2,
                    p: 0.75,
                    bgcolor: theme.palette.action.hover,
                    transition: 'background-color 0.2s',
                    '&:hover': { bgcolor: theme.palette.action.selected },
                  }}
                >
                  <WeatherDisplay code={event.weatherData?.weatherCode} theme={theme.palette.mode} size={32} />
                </Box>
              </Tooltip>
              {event.permissions?.canViewRides && (
              <Tooltip
                title={
                  teamRideStatus === 'none'
                    ? 'Keine Mitfahrgelegenheiten'
                    : teamRideStatus === 'full'
                    ? 'Alle Plätze belegt'
                    : 'Plätze frei – klicken für Details'
                }
                arrow
              >
                <Box
                  id="teamride-information"
                  onClick={() => openTeamRideDetails(event.id)}
                  sx={{
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    borderRadius: 2,
                    p: 0.75,
                    bgcolor: theme.palette.action.hover,
                    transition: 'background-color 0.2s',
                    '&:hover': { bgcolor: theme.palette.action.selected },
                    position: 'relative',
                  }}
                >
                  <FaCar
                    size={24}
                    style={{
                      color:
                        teamRideStatus === 'none'
                          ? theme.palette.text.disabled
                          : teamRideStatus === 'full'
                          ? theme.palette.error.main
                          : theme.palette.success.main,
                    }}
                  />
                  {teamRideStatus === 'free' && (
                    <Box
                      sx={{
                        position: 'absolute',
                        top: 2,
                        right: 2,
                        width: 8,
                        height: 8,
                        borderRadius: '50%',
                        bgcolor: 'success.main',
                        border: '2px solid',
                        borderColor: 'background.paper',
                      }}
                    />
                  )}
                </Box>
              </Tooltip>
              )}
            </Stack>
          </Paper>

          {/* --- Location --- */}
          {event.location?.name && (
            <Box sx={{ px: 0.5 }}>
              <Location
                id={0}
                name={event.location.name}
                latitude={event.location.latitude}
                longitude={event.location.longitude}
                address={`${event.location.city || ''}, ${event.location.address || ''}`.trim()}
              />
            </Box>
          )}

          {/* --- Game Matchup --- */}
          {event.game && (
            <Paper
              variant="outlined"
              sx={{
                p: 2,
                borderRadius: 2,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: 2,
                bgcolor: theme.palette.mode === 'dark' ? 'grey.900' : 'grey.50',
                position: 'relative',
                overflow: 'hidden',
              }}
            >
              {event.game.gameType?.name && (
                <Chip
                  label={event.game.gameType.name}
                  size="small"
                  variant="outlined"
                  sx={{ position: 'absolute', top: 8, right: 8, fontSize: '0.7rem' }}
                />
              )}
              <Box sx={{ textAlign: 'center', flex: 1 }}>
                <Avatar sx={{ bgcolor: typeColor, mx: 'auto', mb: 0.5, width: 36, height: 36 }}>
                  <SportsSoccerIcon sx={{ fontSize: 20 }} />
                </Avatar>
                <Typography variant="body2" fontWeight={700}>
                  {event.game.homeTeam?.name || '–'}
                </Typography>
              </Box>
              <Typography variant="h6" fontWeight={800} color="text.secondary" sx={{ userSelect: 'none' }}>
                vs
              </Typography>
              <Box sx={{ textAlign: 'center', flex: 1 }}>
                <Avatar sx={{ bgcolor: 'text.disabled', mx: 'auto', mb: 0.5, width: 36, height: 36 }}>
                  <SportsSoccerIcon sx={{ fontSize: 20 }} />
                </Avatar>
                <Typography variant="body2" fontWeight={700}>
                  {event.game.awayTeam?.name || '–'}
                </Typography>
              </Box>
            </Paper>
          )}

          {/* --- Description --- */}
          {event.description && (
            <Paper
              variant="outlined"
              sx={{ p: 2, borderRadius: 2 }}
            >
              <Stack direction="row" spacing={1} alignItems="flex-start">
                <DescriptionIcon sx={{ fontSize: 20, color: 'text.secondary', mt: 0.25 }} />
                <Typography variant="body2" color="text.primary" sx={{ whiteSpace: 'pre-wrap' }}>
                  {event.description}
                </Typography>
              </Stack>
            </Paper>
          )}

          <Divider sx={{ my: 0.5 }} />

          {event.permissions?.canParticipate && (
          <>
          {/* --- Participation Section --- */}
          <Box>
            <Stack direction="row" spacing={1} alignItems="center" mb={1.5}>
              <GroupIcon sx={{ fontSize: 22, color: typeColor }} />
              <Typography variant="subtitle1" fontWeight={700}>Teilnahme</Typography>
            </Stack>

            {loading ? (
              <Box display="flex" justifyContent="center" py={3}>
                <CircularProgress size={32} />
              </Box>
            ) : (
              <>
                {/* Participation Buttons — hidden when cancelled */}
                {!event.cancelled && (
                  <>
                    {/* Current participation status badge */}
                    {currentParticipation && (
                      <Paper
                        variant="outlined"
                        sx={{
                          mb: 2,
                          p: 1.25,
                          borderRadius: 2,
                          borderColor: currentParticipation.color || 'divider',
                          bgcolor: `${currentParticipation.color || '#888'}18`,
                          display: 'flex',
                          alignItems: 'flex-start',
                          gap: 1,
                        }}
                      >
                        <CheckCircleOutlineIcon
                          sx={{ fontSize: 18, color: currentParticipation.color || 'text.secondary', mt: 0.15, flexShrink: 0 }}
                        />
                        <Box sx={{ minWidth: 0 }}>
                          <Typography variant="body2" fontWeight={600} sx={{ color: currentParticipation.color || 'text.primary' }}>
                            {currentParticipation.statusName}
                          </Typography>
                          {currentParticipation.note && (
                            <Typography
                              variant="caption"
                              color="text.secondary"
                              sx={{ display: 'block', fontStyle: 'italic', wordBreak: 'break-word' }}
                            >
                              {currentParticipation.note}
                            </Typography>
                          )}
                        </Box>
                        <Button
                          size="small"
                          variant="text"
                          startIcon={<ChatBubbleOutlineIcon sx={{ fontSize: 14 }} />}
                          onClick={() => handleStatusClick(currentParticipation.statusId)}
                          disabled={saving}
                          sx={{
                            ml: 'auto',
                            textTransform: 'none',
                            fontSize: '0.75rem',
                            flexShrink: 0,
                            color: 'text.secondary',
                          }}
                        >
                          Notiz
                        </Button>
                      </Paper>
                    )}
                    <Stack
                      id="event-action-button"
                      direction="row"
                      spacing={1}
                      mb={2}
                      sx={{ flexWrap: 'wrap', gap: 1 }}
                    >
                  {Array.isArray(participationStatuses) && participationStatuses.length > 0 && (
                    participationStatuses
                      .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
                      .map(status => {
                        const isActive = currentParticipation?.statusId === status.id;
                        return (
                          <Button
                            key={status.id}
                            variant={isActive ? 'contained' : 'outlined'}
                            size={isMobile ? 'medium' : 'small'}
                            startIcon={status.icon ? <i className={`fa fa-${status.icon} fa-fw`} /> : undefined}
                            disabled={saving}
                            onClick={() => handleStatusClick(status.id)}
                            sx={{
                              borderRadius: 6,
                              textTransform: 'none',
                              fontWeight: isActive ? 700 : 500,
                              minWidth: isMobile ? 96 : undefined,
                              bgcolor: isActive ? (status.color || undefined) : undefined,
                              color: isActive ? '#fff' : (status.color || undefined),
                              borderColor: status.color || undefined,
                              '&:hover': {
                                bgcolor: isActive
                                  ? (status.color || undefined)
                                  : `${status.color || theme.palette.primary.main}1A`,
                                borderColor: status.color || undefined,
                              },
                            }}
                          >
                            {status.name}
                          </Button>
                        );
                      })
                  )}
                </Stack>
                  </>
                )}
                {/* Participant List - Collapsible */}
                <Box id="participations-list">
                  <Stack
                    direction="row"
                    spacing={1}
                    alignItems="center"
                    onClick={() => setParticipantsExpanded(!participantsExpanded)}
                    sx={{ cursor: 'pointer', userSelect: 'none', mb: 1 }}
                  >
                    <Typography variant="subtitle2" fontWeight={600}>
                      Teilnehmer ({participations.length})
                    </Typography>
                    {participantsExpanded ? <ExpandLessIcon fontSize="small" /> : <ExpandMoreIcon fontSize="small" />}
                  </Stack>
                  <Collapse in={participantsExpanded}>
                    {participations.length === 0 ? (
                      <Typography variant="body2" color="text.secondary" sx={{ py: 1 }}>
                        Noch keine Rückmeldungen.
                      </Typography>
                    ) : (
                      <Stack spacing={1.5}>
                        {Object.values(groupedParticipations).map(group => (
                          <Box key={group.statusName}>
                            <Stack direction="row" spacing={0.75} alignItems="center" mb={0.5}>
                              <Box
                                sx={{
                                  width: 10,
                                  height: 10,
                                  borderRadius: '50%',
                                  bgcolor: group.color || 'text.secondary',
                                  flexShrink: 0,
                                }}
                              />
                              <Typography variant="caption" fontWeight={700} sx={{ textTransform: 'uppercase', letterSpacing: 0.5 }}>
                                {group.statusName} ({group.participants.length})
                              </Typography>
                            </Stack>
                            <Stack spacing={0.75}>
                              {group.participants.map(p => (
                                <Box
                                  key={p.user_id}
                                  sx={{
                                    display: 'flex',
                                    alignItems: p.note ? 'flex-start' : 'center',
                                    gap: 1,
                                    py: p.note ? 0.5 : 0,
                                  }}
                                >
                                  <Chip
                                    label={p.user_name}
                                    size="small"
                                    variant="outlined"
                                    sx={{
                                      borderColor: group.color || undefined,
                                      fontSize: '0.75rem',
                                      height: 26,
                                      flexShrink: 0,
                                    }}
                                  />
                                  {p.note && (
                                    <Typography
                                      variant="caption"
                                      color="text.secondary"
                                      sx={{
                                        fontStyle: 'italic',
                                        lineHeight: 1.4,
                                        wordBreak: 'break-word',
                                      }}
                                    >
                                      {p.note}
                                    </Typography>
                                  )}
                                </Box>
                              ))}
                            </Stack>
                          </Box>
                        ))}
                      </Stack>
                    )}
                  </Collapse>
                </Box>
              </>
            )}
          </Box>
          </>)}
        </Box>
      </BaseModal>

      <WeatherModal
        open={weatherModalOpen}
        onClose={() => setWeatherModalOpen(false)}
        eventId={selectedEventId}
      />

      <TeamRideDetailsModal
        open={teamRideModalOpen}
        onClose={() => setTeamRideModalOpen(false)}
        eventId={selectedEventId}
        cancelled={event?.cancelled}
      />

      <TourTooltip steps={tourSteps} />

      {/* Note Dialog — opens when user clicks a participation status button */}
      {(() => {
        const pendingStatus = participationStatuses.find(s => s.id === pendingStatusId);
        return (
          <Dialog
            open={noteDialogOpen}
            onClose={() => setNoteDialogOpen(false)}
            maxWidth="xs"
            fullWidth
            PaperProps={{ sx: { borderRadius: isMobile ? 0 : 3, m: isMobile ? 0 : 2, width: '100%' } }}
            sx={isMobile ? { '& .MuiDialog-container': { alignItems: 'flex-end' } } : {}}
          >
            <DialogTitle sx={{ pb: 1 }}>
              <Stack direction="row" spacing={1} alignItems="center">
                {pendingStatus?.color && (
                  <Box
                    sx={{
                      width: 14,
                      height: 14,
                      borderRadius: '50%',
                      bgcolor: pendingStatus.color,
                      flexShrink: 0,
                    }}
                  />
                )}
                <Typography variant="subtitle1" fontWeight={700}>
                  {pendingStatus?.name ?? 'Rückmeldung'}
                </Typography>
              </Stack>
            </DialogTitle>
            <DialogContent sx={{ pt: '8px !important' }}>
              <Typography variant="body2" color="text.secondary" mb={1.5}>
                Möchtest du eine Nachricht hinzufügen? (optional)
              </Typography>
              <TextField
                id="participation-note"
                label="Nachricht (optional)"
                value={dialogNote}
                onChange={e => setDialogNote(e.target.value)}
                fullWidth
                multiline
                minRows={3}
                maxRows={6}
                autoFocus
                inputProps={{ maxLength: 300 }}
                placeholder={
                  'z.B. Komme 10 Minuten später\nBringe Eierschecke mit\n…'
                }
                sx={{ '& .MuiOutlinedInput-root': { borderRadius: 2 } }}
                helperText={`${dialogNote.length}/300`}
              />
            </DialogContent>
            <DialogActions sx={{ px: 2.5, pb: 2.5, gap: 1 }}>
              <Button
                onClick={() => setNoteDialogOpen(false)}
                sx={{ borderRadius: 2, flex: 1 }}
              >
                Abbrechen
              </Button>
              <Button
                onClick={handleParticipationSubmit}
                variant="contained"
                sx={{
                  borderRadius: 2,
                  flex: 2,
                  bgcolor: pendingStatus?.color || undefined,
                  '&:hover': { bgcolor: pendingStatus?.color ? `${pendingStatus.color}cc` : undefined },
                }}
              >
                Bestätigen
              </Button>
            </DialogActions>
          </Dialog>
        );
      })()}

      {/* Cancel Dialog */}
      <Dialog open={cancelDialogOpen} onClose={() => setCancelDialogOpen(false)} maxWidth="sm" fullWidth>
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <CancelIcon color="warning" />
          Event absagen
        </DialogTitle>
        <DialogContent>
          <Typography variant="body2" sx={{ mb: 2 }}>
            Alle Teilnehmer und Fahrer/Mitfahrer werden per Push-Benachrichtigung informiert.
          </Typography>
          <TextField
            label="Grund der Absage *"
            value={cancelReason}
            onChange={e => setCancelReason(e.target.value)}
            fullWidth
            multiline
            minRows={2}
            maxRows={5}
            autoFocus
            placeholder="z.B. Schlechtes Wetter, Platz gesperrt, ..."
            sx={{ '& .MuiOutlinedInput-root': { borderRadius: 2 } }}
          />
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 2 }}>
          <Button onClick={() => setCancelDialogOpen(false)} disabled={cancelling} sx={{ borderRadius: 2 }}>
            Abbrechen
          </Button>
          <Button
            onClick={handleCancelEvent}
            color="warning"
            variant="contained"
            disabled={cancelling || !cancelReason.trim()}
            startIcon={<CancelIcon />}
            sx={{ borderRadius: 2 }}
          >
            {cancelling ? 'Wird abgesagt...' : 'Absagen'}
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};
