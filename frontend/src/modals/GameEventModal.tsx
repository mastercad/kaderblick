// Hilfsfunktion: Sekunden zu MM:SS
const secondsToMMSS = (seconds) => {
  if (!seconds || isNaN(seconds)) return '0:00';
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${s.toString().padStart(2, '0')}`;
};
// Hilfsfunktion: MM:SS oder Sekunden-String zu Sekunden
const parseMinuteInput = (input) => {
  if (!input) return 0;
  if (/^\d+$/.test(input)) return parseInt(input, 10);
  const parts = input.split(':').map(Number);
  if (parts.length === 2) return parts[0] * 60 + parts[1];
  if (parts.length === 3) return parts[0] * 3600 + parts[1] * 60 + parts[2];
  return 0;
};
import React, { useState, useEffect, useRef } from 'react';
import { apiJson } from '../utils/api';
import {
  Button,
  TextField,
  Select,
  MenuItem,
  InputLabel,
  FormControl,
  Box,
  Typography
} from '@mui/material';
import { 
  fetchGameEventTypes,
  fetchSubstitutionReasons,
  createGameEvent,
  updateGameEvent
} from '../services/games';
import { Game, GameEvent, GameEventType, Player, SubstitutionReason } from '../types/games';
import { getGameEventIconByCode } from '../constants/gameEventIcons';
import BaseModal from './BaseModal';

interface GameEventModalProps {
  open: boolean;
  onClose: () => void;
  onSuccess: () => void;
  gameId: number;
  game: Game;
  existingEvent?: GameEvent | null;
  initialMinute?: number;
}

export const GameEventModal: React.FC<GameEventModalProps> = ({
  open,
  onClose,
  onSuccess,
  gameId,
  game,
  existingEvent = null,
  initialMinute
}) => {
  // Helper: InitialformData abhängig von Props
  const getInitialFormData = () => {
    if (existingEvent) {
      let minuteStr = '0:00';
      if (existingEvent.timestamp && game.calendarEvent?.startDate) {
        // Zeitdifferenz in Sekunden zu MM:SS
        const eventTime = new Date(existingEvent.timestamp);
        const gameStart = new Date(game.calendarEvent.startDate);
        const diffSec = Math.floor((eventTime.getTime() - gameStart.getTime()) / 1000);
        minuteStr = secondsToMMSS(diffSec);
      } else if (existingEvent.minute && !isNaN(Number(existingEvent.minute))) {
        // Spielminute als MM:00 anzeigen
        minuteStr = `${existingEvent.minute}:00`;
      }
      return {
        team: existingEvent.team?.id?.toString() || existingEvent.teamId?.toString() || '',
        eventType: existingEvent.gameEventType?.id?.toString() || existingEvent.typeId?.toString() || '',
        player: existingEvent.player?.id?.toString() || existingEvent.playerId?.toString() || '',
        relatedPlayer: existingEvent.relatedPlayer?.id?.toString() || existingEvent.relatedPlayerId?.toString() || '',
        minute: minuteStr,
        description: existingEvent.description || '',
        reason: (existingEvent as any).reason?.id || 0,
        playerId: existingEvent.playerId || 0,
        teamId: existingEvent.teamId || 0
      };
    } else {
      return {
        team: '',
        eventType: '',
        player: '',
        relatedPlayer: '',
        minute: initialMinute !== undefined ? secondsToMMSS(initialMinute) : '',
        description: '',
        reason: 0,
        playerId: 0,
        teamId: 0,
      };
    }
  };
  const [loading, setLoading] = useState(false);
  const [eventTypes, setEventTypes] = useState<GameEventType[]>([]);
  const [players, setPlayers] = useState<Player[]>([]);
  const [substitutionReasons, setSubstitutionReasons] = useState<SubstitutionReason[]>([]);
  const [teamPlayers, setTeamPlayers] = useState<Player[]>([]);
  const [formData, setFormData] = useState(getInitialFormData);
  const prevOpen = useRef(false);

  useEffect(() => {
    if (open && !prevOpen.current) {
      setFormData(getInitialFormData());
    }
    prevOpen.current = open;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, existingEvent, initialMinute]);

  // Wenn initialMinute sich ändert und wir KEIN bestehendes Event bearbeiten, minute übernehmen
  useEffect(() => {
    if (open && !existingEvent && typeof initialMinute === 'number') {
      setFormData(prev => ({ ...prev, minute: secondsToMMSS(initialMinute) }));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialMinute, open, existingEvent]);

  // Spieler für Team laden, wenn Team gewechselt wird
  useEffect(() => {
    if (formData.team) {
      apiJson(`/api/teams/${formData.team}/players`).then(players => {
        setTeamPlayers(players);
      });
    } else {
      setTeamPlayers([]);
    }
  }, [formData.team]);

  // Zeit-Logik
  const [currentTime, setCurrentTime] = useState(new Date());
  const [elapsedSeconds, setElapsedSeconds] = useState(0);
  const timerRef = useRef<number | null>(null);

  // Annahme: game.calendarEvent?.startDate ist Startzeit
  useEffect(() => {
    if (open && game.calendarEvent?.startDate) {
      setCurrentTime(new Date());
      const start = new Date(game.calendarEvent.startDate);
      setElapsedSeconds(Math.floor((Date.now() - start.getTime()) / 1000));
      timerRef.current = setInterval(() => {
        setCurrentTime(new Date());
        setElapsedSeconds(Math.floor((Date.now() - start.getTime()) / 1000));
      }, 1000);
      return () => {
        if (timerRef.current) clearInterval(timerRef.current);
      };
    }
    // eslint-disable-next-line
  }, [open, game.calendarEvent?.startDate]);

  useEffect(() => {
    if (open) {
      loadInitialData();
      // Wenn ein bestehendes Event bearbeitet wird, minute als MM:SS oder MM:00 anzeigen
      if (existingEvent) {
        let minuteStr = '0:00';
        if (existingEvent.timestamp && game.calendarEvent?.startDate) {
          const eventTime = new Date(existingEvent.timestamp);
          const gameStart = new Date(game.calendarEvent.startDate);
          const diffSec = Math.floor((eventTime.getTime() - gameStart.getTime()) / 1000);
          minuteStr = secondsToMMSS(diffSec);
        } else if (existingEvent.minute && !isNaN(Number(existingEvent.minute))) {
          minuteStr = `${existingEvent.minute}:00`;
        }
        setFormData(prev => ({ ...prev, minute: minuteStr }));
      }
    }
  }, [open, existingEvent, game]);

  const loadInitialData = async () => {
    try {
//      const [eventTypesRaw, playersData, reasonsData] = await Promise.all([
      const [eventTypesRaw, reasonsData] = await Promise.all([
        fetchGameEventTypes(),
//        fetchPlayersForTeams([game.homeTeam.id, game.awayTeam.id]),
        fetchSubstitutionReasons()
      ]);

      // Support both array and {entries: []} API response for event types
      const eventTypesData = Array.isArray(eventTypesRaw)
        ? eventTypesRaw
        : ((eventTypesRaw as any).gameEventTypes || []);
      setEventTypes(eventTypesData);
//      setPlayers(playersData);
      setPlayers([]);
      setSubstitutionReasons(reasonsData);
    } catch (error) {
      console.error('Error loading initial data:', error);
    }
  };

  // Hilfsfunktion: Sekunden zu MM:SS
  const secondsToMMSS = (seconds: number) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
  };
  // Hilfsfunktion: MM:SS oder Sekunden-String zu Sekunden
  const parseMinuteInput = (input: string) => {
    if (!input) return 0;
    if (/^\d+$/.test(input)) return parseInt(input, 10);
    const parts = input.split(':').map(Number);
    if (parts.length === 2) return parts[0] * 60 + parts[1];
    if (parts.length === 3) return parts[0] * 3600 + parts[1] * 60 + parts[2];
    return 0;
  };
  const handleInputChange = (field: string, value: string | number) => {
    if (field === 'minute') {
      // Nur erlaubte Zeichen (Zahlen, :)
      let val = String(value).replace(/[^\d:]/g, '');
      setFormData(prev => ({ ...prev, minute: val }));
      return;
    }
    // Wenn Team gewechselt wird, Spielerfelder zurücksetzen
    if (field === 'team') {
      setFormData(prev => ({
        ...prev,
        team: String(value),
        player: '',
        relatedPlayer: ''
      }));
    } else {
      setFormData(prev => ({ ...prev, [field]: value }));
    }
  };

  // Spieler für aktuelles Team anzeigen
  const filteredPlayers = formData.team ? teamPlayers : [];

  const isSubstitution = () => {
    const selectedEventType = eventTypes.find(et => et.id === Number(formData.eventType));
    if (!selectedEventType) return false;
    const name = selectedEventType.name.toLowerCase();
    const code = selectedEventType.code.toLowerCase();
    return name.includes('wechsel') || code.includes('sub');
  };

  const handleSubmit = async () => {
    try {
      setLoading(true);
      // Minute als Sekunden speichern, aber als string übergeben
      const minuteSec = parseMinuteInput(formData.minute);
      const submitData = {
        eventType: Number(formData.eventType),
        player: formData.player ? Number(formData.player) : undefined,
        relatedPlayer: formData.relatedPlayer ? Number(formData.relatedPlayer) : undefined,
        minute: `${minuteSec}`,
        description: formData.description,
        reason: formData.reason ? Number(formData.reason) : undefined
      };
      if (existingEvent) {
        await updateGameEvent(gameId, existingEvent.id, submitData);
      } else {
        await createGameEvent(gameId, submitData);
      }
      onSuccess();
    } catch (error) {
      console.error('Error saving game event:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleClose = () => {
    setFormData({
      team: '',
      eventType: '',
      player: '',
      relatedPlayer: '',
      minute: '',
      description: '',
      reason: 0,
      teamId: 0,
      playerId: 0,
    });
    onClose();
  };

  return (
    <BaseModal
      open={open}
      onClose={handleClose}
      title={existingEvent ? 'Ereignis bearbeiten' : 'Neues Spielereignis'}
      maxWidth="md"
      actions={
        <>
          <Button onClick={handleClose} disabled={loading} variant="outlined" color="secondary">
            Abbrechen
          </Button>
          <Button
            onClick={handleSubmit}
            variant="contained"
            color="primary"
            disabled={loading || !formData.eventType || !formData.team || !formData.player || !formData.minute}
          >
            {loading ? 'Speichere...' : 'Speichern'}
          </Button>
        </>
      }
    >
      <Box>
        <Typography variant="h6" gutterBottom>
          {game.homeTeam.name} vs {game.awayTeam.name}
        </Typography>
        <Box sx={{ display: 'flex', flexDirection: { xs: 'column', md: 'row' }, gap: 3, mb: 2 }}>
          {/* Linke Spalte: Event-Details */}
          <Box sx={{ flex: 1, minWidth: 0 }}>
            <FormControl fullWidth required sx={{ mb: 2 }}>
              <InputLabel>Team</InputLabel>
              <Select
                value={formData.team}
                onChange={e => handleInputChange('team', e.target.value)}
                label="Team"
              >
                <MenuItem value="">Team wählen…</MenuItem>
                <MenuItem value={game.homeTeam.id}>{game.homeTeam.name}</MenuItem>
                <MenuItem value={game.awayTeam.id}>{game.awayTeam.name}</MenuItem>
              </Select>
            </FormControl>
            <FormControl fullWidth required sx={{ mb: 2 }}>
              <InputLabel>Event-Typ</InputLabel>
              <Select
                value={formData.eventType}
                onChange={e => handleInputChange('eventType', e.target.value)}
                label="Event-Typ"
              >
                <MenuItem value="">Event-Typ wählen…</MenuItem>
                {eventTypes.map((type) => (
                  <MenuItem key={type.id} value={type.id}>
                    <span style={{ color: type.color, marginLeft: 8 }}>{ getGameEventIconByCode(type.code) }</span>
                    {type.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
            <FormControl fullWidth required sx={{ mb: 2 }}>
              <InputLabel>Spieler</InputLabel>
              <Select
                value={formData.player}
                onChange={e => handleInputChange('player', e.target.value)}
                label="Spieler"
              >
                <MenuItem value="">Spieler wählen…</MenuItem>
                {Object.values(filteredPlayers).map((player) => {
                  const shirtNumber = (player as any).shirtNumber;
                  const fullName = (player as any).fullName ?? `${(player as any).firstName ?? ''} ${(player as any).lastName ?? ''}`.trim();
                  return (
                    <MenuItem key={player.id} value={player.id}>
                      {shirtNumber ? `#${shirtNumber} ` : ''}{fullName}
                    </MenuItem>
                  );
                })}
              </Select>
            </FormControl>
            {/* Zweiter Spieler (bei Wechsel) */}
            {isSubstitution() && (
              <FormControl fullWidth sx={{ mb: 2 }}>
                <InputLabel>Zweiter Spieler (bei Wechsel)</InputLabel>
                <Select
                  value={formData.relatedPlayer}
                  onChange={e => handleInputChange('relatedPlayer', e.target.value)}
                  label="Zweiter Spieler (bei Wechsel)"
                >
                  <MenuItem value="">Zweiter Spieler wählen…</MenuItem>
                  {Object.values(filteredPlayers).map((player) => {
                    const shirtNumber = (player as any).shirtNumber;
                    const fullName = (player as any).fullName ?? `${(player as any).firstName ?? ''} ${(player as any).lastName ?? ''}`.trim();
                    return (
                      <MenuItem key={player.id} value={player.id}>
                        {shirtNumber ? `#${shirtNumber} ` : ''}{fullName}
                      </MenuItem>
                    );
                  })}
                </Select>
              </FormControl>
            )}
            {/* Grund für Wechsel */}
            {isSubstitution() && (
              <FormControl fullWidth sx={{ mb: 2 }}>
                <InputLabel>Grund für Wechsel</InputLabel>
                <Select
                  value={formData.reason}
                  onChange={e => handleInputChange('reason', e.target.value)}
                  label="Grund für Wechsel"
                >
                  <MenuItem value="">Grund für Wechsel wählen…</MenuItem>
                  {substitutionReasons.map((reason) => (
                    <MenuItem key={reason.id} value={reason.id}>
                      {reason.name}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            )}
          </Box>
          {/* Rechte Spalte: Zeit */}
          <Box sx={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column', alignItems: 'flex-end', justifyContent: 'flex-start' }}>
            <Typography variant="h3" fontWeight="bold" sx={{ mb: 2 }}>{currentTime.toLocaleTimeString()}</Typography>
            <Typography variant="body2">Seit Startzeit vergangene Sekunden:</Typography>
            <Button
              variant="outlined"
              color="primary"
              sx={{ fontSize: '1.5rem', mt: 1, mb: 2 }}
              onClick={() => setFormData(prev => ({ ...prev, minute: elapsedSeconds.toString() }))}
            >
              {elapsedSeconds} <span style={{ marginLeft: 8 }}><i className="fas fa-clock" /></span>
            </Button>
          </Box>
        </Box>
        {/* Neue Zeile: Zeit und Beschreibung */}
        <Box sx={{ display: 'flex', gap: 2, mb: 2 }}>
          <TextField
            label="Zeit (MM:SS oder Sekunden)"
            value={formData.minute ?? ''}
            onChange={e => handleInputChange('minute', e.target.value)}
            fullWidth
            required
            inputProps={{ style: { textAlign: 'right' } }}
            sx={{ maxWidth: 180 }}
            placeholder="z.B. 5:08"
          />
          <TextField
            label="Beschreibung (optional)"
            value={formData.description}
            onChange={e => handleInputChange('description', e.target.value)}
            fullWidth
          />
        </Box>
      </Box>
    </BaseModal>
  );
};
