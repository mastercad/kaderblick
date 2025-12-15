import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Chip from '@mui/material/Chip';
import Stack from '@mui/material/Stack';
import Divider from '@mui/material/Divider';
import React, { useState, useEffect } from 'react';
import CircularProgress from '@mui/material/CircularProgress';
import { apiJson } from '../utils/api';
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
    };
  } | null;

  onEdit?: () => void;
  showEdit?: boolean;
  onDelete?: () => void;

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
}

export const EventDetailsModal: React.FC<EventDetailsModalProps> = ({
  open,
  onClose,
  event,
  onEdit,
  showEdit = false,
  onDelete,
}) => {
  const [participationStatuses, setParticipationStatuses] = useState<Array<any>>([]);
  const [currentParticipation, setCurrentParticipation] = useState<any>(null);
  const [participations, setParticipations] = useState<Array<any>>([]);
  const [note, setNote] = useState('');
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [weatherModalOpen, setWeatherModalOpen] = useState(false);
  const [selectedEventId, setSelectedEventId] = useState<number | null>(null);
  const [teamRideModalOpen, setTeamRideModalOpen] = useState(false);
  const [teamRideStatus, setTeamRideStatus] = useState<'none'|'full'|'free'>('none');

  const tourSteps = [
    {
      target: "event-details",
      content: "Hier findest du alle wichtigen Infos zum Event. Schau dich ruhig um!",
    },
    {
      target: "event-action-button",
      content: "Mit diesem Button kannst du dich zum Event anmelden oder abmelden!",
    },
    {
      target: "participation-note",
      content: "Teile hier optional mit, warum du diesen Status gesetzt hast.",
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
    if (!event?.id || !open) {
      setTeamRideStatus('none');
      return;
    }
    apiJson(`/api/teamrides/event/${event.id}`)
      .then(data => {
        const rides = data.rides || [];
        if (rides.length === 0) {
          setTeamRideStatus('none');
        } else if (rides.every(r => r.availableSeats === 0)) {
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
    if (!event || !open) return;
    setLoading(true);
    Promise.all([
      apiJson(`/api/participation/statuses`),
      apiJson(`/api/participation/event/${event.id}`).catch(() => []),
    ])
      .then(([statusesResponse, list]) => {
        if (!Array.isArray(statusesResponse.statuses)) {
          console.error('FEHLER: participation/statuses liefert kein Array!', statusesResponse);
        }
        setParticipationStatuses(Array.isArray(statusesResponse.statuses) ? statusesResponse.statuses : []);
        setParticipations(list.participations);
      })
      .catch(() => {
        setParticipationStatuses([]);
        setCurrentParticipation(null);
        setParticipations([]);
        setNote('');
      })
      .finally(() => setLoading(false));
  }, [event, open]);

  const handleParticipationChange = (statusId: number) => {
    if (!event) return;
    setSaving(true);
    apiJson(`/api/participation/event/${event.id}/respond`, {
      method: 'POST',
      body: { status_id: statusId, note },
    })
      .then(() => {
        return Promise.all([
          apiJson(`/api/participation/event/${event.id}`),
        ]);
      })
      .then(([list]) => {
        setParticipations(list.participations);
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

  if (!event) return null;

  return (
    <>
      <BaseModal
        open={open}
        onClose={onClose}
        title={event.title}
        maxWidth="md"
        actions={
          <>
            <Button onClick={onClose} color="primary" variant="contained">
              Schließen
            </Button>
            {event.permissions?.canDelete && onDelete && (
              <Button onClick={onDelete} color="error" variant="outlined">
                Löschen
              </Button>
            )}
            {((showEdit && onEdit) || event.permissions?.canEdit) && (
              <Button onClick={onEdit} color="secondary" variant="outlined">
                Bearbeiten
              </Button>
            )}
          </>
        }
      >
        <Box id="event-details">
          <Box display="flex" justifyContent="space-between" alignItems="center">
            <Box mb={2}>
              <Typography variant="subtitle2" color="text.secondary">
                {event.type?.name && (
                  <span style={{ color: event.type.color || undefined, fontWeight: 600 }}>{event.type.name}</span>
                )}
                {event.location?.name && (
                  <>
                    <br />
                    <Location 
                      name={event.location.name} 
                      latitude={event.location.latitude} 
                      longitude={event.location.longitude}
                      address={`${event.location.city}, ${event.location.address}`.trim()}
                    />
                  </>
                )}
              </Typography>
              <Typography variant="body2" color="text.secondary" mt={1}>
                {(() => {
                  const startDate = new Date(event.start);
                  const endDate = new Date(event.end);
                  const isSameDay = startDate.toDateString() === endDate.toDateString();
                  const startFormatted = startDate.toLocaleString('de-DE', {
                    weekday: 'short',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  });
                  if (isSameDay) {
                    const endTimeFormatted = endDate.toLocaleString('de-DE', {
                      hour: '2-digit',
                      minute: '2-digit',
                    });
                    return `${startFormatted} – ${endTimeFormatted}`;
                  } else {
                    const endFormatted = endDate.toLocaleString('de-DE', {
                      weekday: 'short',
                      day: '2-digit',
                      month: '2-digit',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                    });
                    return `${startFormatted} – ${endFormatted}`;
                  }
                })()}
              </Typography>
            </Box>
            <Box>
              <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', pr: 2, pt: 1 }}
                onClick={() => {
                  openWeatherModal(event.id);
                }}
                id="weather-information"
                >
                <span style={{ cursor: 'pointer', marginRight: 8 }} title="Wetterdetails anzeigen">
                  <WeatherDisplay 
                    code={event.weatherData?.weatherCode} theme={'light'}
                  />
                </span>
              </Box>
              <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', pr: 2, pt: 1 }}
                onClick={() => {
                  openTeamRideDetails(event.id);
                }}
                id="teamride-information"
                >
                  <FaCar 
                    size={32}
                    style={{ 
                      cursor: 'pointer', 
                      marginRight: 8,
                      color: teamRideStatus === 'none' ? '#888' : teamRideStatus === 'full' ? '#d32f2f' : '#388e3c',
                      opacity: teamRideStatus === 'none' ? 0.6 : 1,
                    }} 
                    title={teamRideStatus === 'none' ? 'Keine Mitfahrgelegenheiten' : teamRideStatus === 'full' ? 'Alle Mitfahrgelegenheiten voll' : 'Plätze frei'}
                  />
              </Box>
            </Box>
          </Box>
        </Box>
        {event.game && (
          <Box mb={2}>
            <Typography variant="subtitle2">Spiel:</Typography>
            <Typography variant="body2">
              {event.game.homeTeam?.name} vs. {event.game.awayTeam?.name} ({event.game.gameType?.name})
            </Typography>
          </Box>
        )}
        {event.description && (
          <Box mb={2}>
            <Typography variant="subtitle2">Beschreibung:</Typography>
            <Typography variant="body2">{event.description}</Typography>
          </Box>
        )}

        {/* Teilnahme-Sektion */}
        <Box>
          <Typography variant="h6" gutterBottom>Teilnahme</Typography>
          {loading ? (
            <Box display="flex" alignItems="center" justifyContent="center" my={2}><CircularProgress /></Box>
          ) : (
            <>
              {/* Aktueller Status */}
              {currentParticipation && (
                <Box id="current-participation-status" mb={2}>
                  <Chip
                    label={
                      <span>
                        {currentParticipation.icon && <i className={currentParticipation.icon} style={{ marginRight: 6 }} />}
                        Ihre aktuelle Teilnahme: <strong style={{ color: currentParticipation.color || '#0dcaf0' }}>{currentParticipation.statusName}</strong>
                      </span>
                    }
                    style={{ background: currentParticipation.color || '#e3f2fd', color: '#222', fontWeight: 500 }}
                  />
                </Box>
              )}
              {/* Teilnahme-Buttons */}
              <Stack id="event-action-button" direction="row" spacing={2} mb={2}>
                {Array.isArray(participationStatuses) && participationStatuses.length > 0 && (
                  participationStatuses.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0)).map(status => (
                    <Button
                      key={status.id}
                      variant={currentParticipation?.statusId === status.id ? 'contained' : 'outlined'}
                      color="primary"
                      startIcon={status.icon ? <i className={`fa fa-${status.icon} fa-fw`} /> : undefined}
                      style={{ background: status.color || undefined, color: status.color ? '#fff' : undefined }}
                      disabled={saving}
                      onClick={() => handleParticipationChange(status.id)}
                    >
                      {status.name}
                    </Button>
                  ))
                )}
              </Stack>
              {/* Notizfeld */}
              <Box mb={2}
                id="participation-note">
                <TextField
                  label="Notiz (optional)"
                  value={note}
                  onChange={e => setNote(e.target.value)}
                  fullWidth
                  multiline
                  minRows={1}
                  maxRows={3}
                  disabled={saving}
                />
              </Box>
              {/* Teilnehmerliste */}
              <Box id="participations-list" mb={2}>
                <Typography variant="subtitle2" gutterBottom>Teilnehmer</Typography>
                {participations.length === 0 ? (
                  <Typography variant="body2" color="text.secondary">Noch keine Rückmeldungen.</Typography>
                ) : (
                  Object.values(groupedParticipations).map(group => (
                    <Box key={group.statusName} mb={1}>
                      <Typography variant="body2" style={{ fontWeight: 600, color: group.color || undefined }}>
                        {group.icon && <i className={group.icon} style={{ marginRight: 4 }} />}
                        {group.statusName} ({group.participants.length})
                      </Typography>
                      <Stack direction="row" spacing={1} flexWrap="wrap">
                        {group.participants.map(p => (
                          <Chip key={p.user_id} label={p.user_name} size="small" style={{ marginBottom: 2 }} />
                        ))}
                      </Stack>
                    </Box>
                  ))
                )}
              </Box>
            </>
          )}
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
      />

      <TourTooltip steps={tourSteps} />
    </>
  );
};
