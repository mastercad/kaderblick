import CalendarFab from '../components/CalendarFab';
import { useFabStack } from '../components/FabStackProvider';
// ErrorBoundary für bessere Fehlerdiagnose
import React from 'react';
import { useEffect, useState, useMemo, useCallback } from 'react';
import { EventDetailsModal } from '../modals/EventDetailsModal';
import { EventModal } from '../modals/EventModal';
import { getEventTypeFlags } from '../hooks/useEventTypeFlags';
import { DynamicConfirmationModal } from '../modals/DynamicConfirmationModal';
import { TaskDeletionModal } from '../modals/TaskDeletionModal';
import { AlertModal } from '../modals/AlertModal';
import { Calendar as BigCalendar, momentLocalizer, Views } from 'react-big-calendar';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import ButtonGroup from '@mui/material/ButtonGroup';
import Button from '@mui/material/Button';
import IconButton from '@mui/material/IconButton';
import Paper from '@mui/material/Paper';
import Stack from '@mui/material/Stack';
import Chip from '@mui/material/Chip';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import ArrowForwardIcon from '@mui/icons-material/ArrowForward';
import TodayIcon from '@mui/icons-material/Today';
import { useTheme } from '@mui/material/styles';
import useMediaQuery from '@mui/material/useMediaQuery';
import { apiJson, apiRequest } from '../utils/api';
import { TournamentGameMode, TournamentType } from '../types/tournament';
import moment from 'moment';
import AddIcon from '@mui/icons-material/Add';
import 'moment/locale/de';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import { League } from '../types/league';

moment.updateLocale('de', {
  week: {
    dow: 1
  }
});

const localizer = momentLocalizer(moment);

class CalendarErrorBoundary extends React.Component<{ children: React.ReactNode }, { hasError: boolean; error: any }> {
  constructor(props: any) {
    super(props);
    this.state = { hasError: false, error: null };
  }
  static getDerivedStateFromError(error: any) {
    return { hasError: true, error };
  }
  componentDidCatch(error: any, info: any) {
    // eslint-disable-next-line no-console
    console.error('Calendar.tsx ErrorBoundary:', error, info);
  }
  render() {
    if (this.state.hasError) {
      return (
        <div style={{ color: 'red', padding: 32 }}>
          <h2>Fehler in Calendar.tsx</h2>
          <pre>{String(this.state.error)}</pre>
        </div>
      );
    }
    return this.props.children;
  }
}

type CalendarEvent = {
  id: number;
  title: string;
  start: Date | string;
  end: Date | string;
  description?: string;
  eventType?: { id: number; name?: string; color?: string };
  location?: { id?: number; name?: string };
  gameType?: { id?: number; name?: string };
  weatherData?: { weatherCode?: number };
  game?: {
    homeTeam?: { id: number; name: string };
    awayTeam?: { id: number; name: string };
  };
  tournamentSettings?: {
    tournamentType?: TournamentType;
    roundDuration?: number;
    breakTime?: number;
    gameMode?: TournamentGameMode;
    numberOfGroups?: number;
  };
  task?: {
    id: number;
    isRecurring: boolean;
    recurrenceMode: string;
    recurrenceRule: string | null;
    rotationUsers: { id: number; fullName: string }[];
    rotationCount: number;
    offset: number;
  };
  tournament?: {
    id?: number;
    matches?: any[];
    teams?: any[];
    settings?: {
      tournamentType?: TournamentType;
      roundDuration?: number;
      breakTime?: number;
      gameMode?: TournamentGameMode;
      numberOfGroups?: number;
    };
  };
  permissionType?: string;
  pendingTournamentMatches?: any[];
  teamIds?: any[];
};

type CalendarEventType = {
  id: number;
  name: string;
  color: string;
};

type Team = {
  id: number;
  name: string;
};

type GameType = {
  id: number;
  name: string;
};

type Location = {
  id: number;
  name: string;
};

type LocationsApiResponse = {
  locations: Location[];
  permissions: {
    canCreate: boolean;
    canEdit: boolean;
    canView: boolean;
    canDelete: boolean;
  };
};

type TeamsApiResponse = {
  teams: Team[];
  permissions: {
    canCreate: boolean;
    canEdit: boolean;
    canView: boolean;
    canDelete: boolean;
  };
};

type EventFormData = {
  title: string;
  date: string;
  time?: string;
  endDate?: string;
  endTime?: string;
  eventType?: string;
  locationId?: string;
  leagueId?: string;
  description?: string;
  homeTeam?: string;
  awayTeam?: string;
  gameType?: string;
  permissionType?: string;
  permissionTeams?: string[];
  permissionClubs?: string[];
  permissionUsers?: string[];
  // Task-bezogene Felder
  task?: {
    id: number;
    isRecurring: boolean;
    recurrenceMode: string;
    recurrenceRule: string | null;
    rotationUsers: { id: number; fullName: string }[];
    rotationCount: number;
    offset: number;
  };
  taskIsRecurring?: boolean;
  taskRecurrenceMode?: string;
  taskFreq?: string;
  taskInterval?: number;
  taskByDay?: string;
  taskByMonthDay?: number;
  taskRecurrenceRule?: string;
  taskRotationUsers?: string[];
  taskRotationCount?: number;
  taskOffset?: number;
  // Tournament-bezogene Felder
  tournamentId?: string;
  tournamentMatchId?: string;
  pendingTournamentMatches?: any[];
  tournamentRoundDuration?: number;
  tournamentBreakTime?: number;
  tournamentGameMode?: TournamentGameMode;
  tournamentType?: TournamentType;
  tournamentNumberOfGroups?: number;
  tournament?: {
    id?: number;
    matches?: any[];
    teams?: any[];
    settings?: {
      tournamentType?: TournamentType;
      tournamentRoundDuration?: number;
      tournamentBreakTime?: number;
      tournamentNumberOfGroups?: number;
      tournamentGameMode?: TournamentGameMode;
    }
  }
  teamIds?: any[];
};

const messages = {
  allDay: 'Ganztägig',
  previous: 'Zurück',
  next: 'Weiter',
  today: 'Heute',
  month: 'Monat',
  week: 'Woche',
  day: 'Tag',
  agenda: 'Agenda',
  date: 'Datum',
  time: 'Zeit',
  event: 'Ereignis',
  noEventsInRange: 'Keine Termine vorhanden. Klicken Sie auf einen Tag, um einen neuen Termin zu erstellen.',
  showMore: (total: number) => `+ ${total} weitere`
};

const formats = {
  agendaDateFormat: 'DD.MM.YYYY',
  agendaTimeFormat: 'HH:mm',
  agendaTimeRangeFormat: ({ start, end }: { start: Date, end: Date }) => {
    return `${moment(start).format('HH:mm')} – ${moment(end).format('HH:mm')}`;
  },
  monthHeaderFormat: 'MMMM YYYY',
  dayHeaderFormat: 'dddd, DD.MM.YYYY',
  dayRangeHeaderFormat: ({ start, end }: { start: Date, end: Date }) => {
    return `${moment(start).format('DD.MM.YYYY')} – ${moment(end).format('DD.MM.YYYY')}`;
  }
};

type CalendarProps = {
  setCalendarFabHandler?: (handler: (() => void) | null) => void;
};

function CalendarInner({ setCalendarFabHandler }: CalendarProps) {
  // Handler für globalen FabStack setzen/entfernen
  const fabStack = useFabStack();
  React.useEffect(() => {
    // CalendarFab nur auf Kalender-Seite anzeigen
    fabStack?.addFab(<CalendarFab onClick={handleAddEvent} />, 'calendar-fab');
    return () => {
      fabStack?.removeFab('calendar-fab');
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const [view, setView] = useState(isMobile ? Views.DAY : Views.MONTH);
  const [date, setDate] = useState(new Date());
  const [selectedEvent, setSelectedEvent] = useState<CalendarEvent | null>(null);
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // Event Modal State
  const [eventModalOpen, setEventModalOpen] = useState(false);
  const [eventFormData, setEventFormData] = useState<EventFormData>({
    title: '',
    date: '',
    time: '',
    eventType: '',
    locationId: '',
    description: '',
    leagueId: '',
    permissionType: 'public',
    taskOffset: 0
  });
  const [editingEventId, setEditingEventId] = useState<number | null>(null);
  const [eventSaving, setEventSaving] = useState(false);
  
  // Zusätzliche Daten
  const [eventTypes, setEventTypes] = useState<{ createAndEditAllowed: boolean; entries: CalendarEventType[] }>({ createAndEditAllowed: false, entries: [] });
  const [teams, setTeams] = useState<Team[]>([]);
  const [gameTypes, setGameTypes] = useState<{ createAndEditAllowed: boolean; entries: GameType[] }>({ createAndEditAllowed: false, entries: [] });
  const [locations, setLocations] = useState<Location[]>([]);
  const [leagues, setLeagues] = useState<League[]>([]);
  const [users, setUsers] = useState<{ id: string | number; fullName?: string; firstName?: string; lastName?: string }[]>([]);

  // Filter State - Set mit aktiven Event-Type-IDs (standardmäßig alle aktiv)
  const [activeEventTypeIds, setActiveEventTypeIds] = useState<Set<number>>(new Set());

  // Modal States
  const [alertModal, setAlertModal] = useState<{
    open: boolean;
    title?: string;
    message: string;
    severity: 'error' | 'warning' | 'info' | 'success';
  }>({
    open: false,
    message: '',
    severity: 'info'
  });

  const [confirmModal, setConfirmModal] = useState<{
    open: boolean;
    title: string;
    message: string;
    onConfirm: () => void;
  }>({
    open: false,
    title: '',
    message: '',
    onConfirm: () => {}
  });

  const [taskDeletionModal, setTaskDeletionModal] = useState<{
    open: boolean;
  }>({
    open: false
  });

  // Mobile views should be limited but include month for better UX
  const availableViews = isMobile ? ['month', 'day', 'agenda'] : ['month', 'week', 'day', 'agenda'];

  // Helper-Funktionen für Modals
  const showAlert = (message: string, severity: 'error' | 'warning' | 'info' | 'success' = 'info', title?: string) => {
    setAlertModal({
      open: true,
      message,
      severity,
      title
    });
  };

  const showConfirm = (message: string, onConfirm: () => void, title: string = 'Bestätigung') => {
    setConfirmModal({
      open: true,
      title,
      message,
      onConfirm
    });
  };

  // Lade Event-Types, Teams, Game-Types, Locations und Users beim ersten Laden
  useEffect(() => {    
    Promise.all([
      apiJson<{ createAndEditAllowed: boolean; entries: CalendarEventType[] }>('/api/calendar-event-types').catch(() => ({ createAndEditAllowed: false, entries: [] })),
      apiJson<TeamsApiResponse[]>('/api/teams/list').catch(() => []),
      apiJson<{ createAndEditAllowed: boolean; entries: GameType[] }>('/api/game-types').catch(() => ({ createAndEditAllowed: false, entries: [] })),
      apiJson<LocationsApiResponse>('/api/locations').catch(() => ({ locations: [], permissions: { canCreate: false, canEdit: false, canView: false, canDelete: false } })),
      apiJson<{ users: { id: string; firstName: string; lastName: string }[] }>('/api/users').catch(() => ({ users: [] }))
    ]).then(([eventTypesData, teamsData, gameTypesData, locationsData, usersData]) => {
      // Defensive: handle possible error objects
      if ('error' in eventTypesData) {
        setEventTypes({ createAndEditAllowed: false, entries: [] });
      } else {
        setEventTypes(eventTypesData);
        // Filtere GameType 'Turnier-Match' aus der Auswahl
        setEventTypes({
          ...eventTypesData,
          entries: (eventTypesData.entries || []).filter(et => et.name !== 'Turnier-Match')
        });
      }
      if (Array.isArray(teamsData)) {
        // Defensive: teamsData might be an array of teams or an array with a single object containing teams
        if (teamsData.length > 0 && 'teams' in teamsData[0]) {
          setTeams((teamsData[0] as any).teams || []);
        } else {
          setTeams([]);
        }
      } else if (teamsData && typeof teamsData === 'object' && 'teams' in teamsData) {
        setTeams((teamsData as any).teams || []);
      } else {
        setTeams([]);
      }
      if ('error' in gameTypesData) {
        setGameTypes({ createAndEditAllowed: false, entries: [] });
      } else {
        // Filtere GameType 'Turnier-Match' aus der Auswahl
        setGameTypes({
          ...gameTypesData,
          entries: (gameTypesData.entries || []).filter(gt => gt.name !== 'Turnier-Match')
        });
      }
      if ('error' in locationsData) {
        setLocations([]);
      } else {
        setLocations(locationsData.locations || []);
      }
      if ('error' in usersData || !usersData.users) {
        setUsers([]);
      } else {
        setUsers(usersData.users || []);
      }
    }).catch(console.error);
  }, []);

  useEffect(() => {
    if (eventTypes.entries.length > 0) {
      setActiveEventTypeIds(new Set(eventTypes.entries.map(et => et.id)));
    }
  }, [eventTypes.entries]);

  useEffect(() => {
    const viewType = view === 'month' ? 'month' : view === 'week' ? 'week' : 'day';
    const start = moment(date).startOf(viewType as any).toISOString();
    const end = moment(date).endOf(viewType as any).toISOString();
    setLoading(true);
    setError(null);
    apiJson(`/api/calendar/events?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`)
      .then((data) => {
        
        console.debug(data);

        const filteredData = (data || []).filter((ev: any) => {
          // Filtere Events basierend auf aktiven Event-Type-IDs
          if (ev.type && activeEventTypeIds.size > 0) {
            return activeEventTypeIds.has(ev.type.id);
          }
          return true;
        });

        function getUniqueValues<T>(iterator: Iterable<T>): T[] {
          const uniqueValues = new Set<T>();
          for (const value of iterator) {
            uniqueValues.add(value.homeTeamId);
            uniqueValues.add(value.awayTeamId);
          }

          return [...uniqueValues];
        }

        setEvents((data || []).map((ev: any) => {
          const tournamentSettings = ev.tournamentSettings || undefined;
          const tournamentMatchesIterator = data.flatMap((event: any) => event.tournament?.matches || []);
          const tournamentTeams = getUniqueValues(tournamentMatchesIterator);
          
          console.debug(tournamentTeams);

          return {
            id: ev.id || 0,
            title: ev.title || 'Unbenannter Termin',
            start: new Date(ev.start),
            end: new Date(ev.end),
            description: ev.description || '',
            eventType: ev.type || {},
            location: ev.location || {},
            game: ev.game || undefined,
            task: ev.task || undefined,
            weatherData: ev.weatherData || undefined,
            tournamentId: ev.tournament?.id,
            tournamentType: tournamentSettings?.type,
            tournamentRoundDuration: tournamentSettings?.roundDuration,
            tournamentBreakTime: tournamentSettings?.breakTime,
            tournamentGameMode: tournamentSettings?.gameMode,
            tournamentNumberOfGroups: tournamentSettings?.numberOfGroups,
            tournament: ev.tournament,
            matches: ev.tournament?.matches,
            pendingTournamentMatches: fullfillPendingTournamentMatches(ev.tournament?.matches || []),
            teamIds: tournamentTeams,
            tournamentTeams: tournamentTeams
          };
        }));
        setLoading(false);
      })
      .catch((e) => {
        setError(e.message);
        setLoading(false);
      });
  }, [date, view]);

  // Event-Handler für das Erstellen neuer Events
  const handleDateClick = (slotInfo: any) => {
    const clickedDate = moment(slotInfo.start).format('YYYY-MM-DD');
    setEventFormData({
      title: '',
      date: clickedDate,
      time: '',
      eventType: '',
      locationId: '',
      description: '',
      permissionType: 'public',
      taskOffset: 0
    });
    setEditingEventId(null);
    setEventModalOpen(true);
  };

  // Event-Handler für das Bearbeiten existierender Events
  const handleEventClick = (info: any) => {
    setSelectedEvent(info);
  };

  const fullfillPendingTournamentMatches = (matches: any[]) => {
    const pendingTournamentMatches = [];

    console.log("FULLFILL PENDING TOURNAMENT MATCHES: ", matches);
    console.log("FULLFILL PENDING TOURNAMENT MATCHES TEAMS: ", teams);

    for (const match of matches) {
      const homeTeam = teams.find(team => String(team.id) === String(match.homeTeamId));
      const awayTeam = teams.find(team => String(team.id) === String(match.awayTeamId));
      if (homeTeam && awayTeam) {
        match.homeTeamName = homeTeam.name;
        match.awayTeamName = awayTeam.name;

        pendingTournamentMatches.push(match);
      }
    }

    console.debug("CALENDAR PENDING TOURNAMENT MATCHES: ", pendingTournamentMatches);

    return pendingTournamentMatches;
  }

  const handleEditEvent = (event: CalendarEvent) => {
    const startDate = moment(event.start);
    const endDate = event.end ? moment(event.end) : null;

    console.debug("Calendar HANDLE EDIT EVENT:", event);

    // Determine gameType: if event type IS "Turnier" but no game entity exists,
    // auto-fill with the "Turnier" gameType for consistent rendering
    let resolvedGameType = (event.game && 'gameType' in event.game && (event.game as any).gameType?.id?.toString())
      || event.gameType?.id?.toString()
      || '';

    if (!resolvedGameType) {
      const eventTypeName = event.eventType?.name?.toLowerCase() || '';
      if (eventTypeName.includes('turnier')) {
        const turnierGT = gameTypes.entries.find(gt => gt.name.toLowerCase().includes('turnier'));
        if (turnierGT) {
          resolvedGameType = turnierGT.id.toString();
        }
      }
    }

    setEventFormData({
      title: event.title,
      date: startDate.format('YYYY-MM-DD'),
      time: startDate.format('HH:mm'),
      endDate: endDate ? endDate.format('YYYY-MM-DD') : '',
      endTime: endDate ? endDate.format('HH:mm') : '',
      eventType: event.eventType?.id?.toString() || '',
      locationId: event.location?.id?.toString(),
      description: event.description || '',
      homeTeam: event.game?.homeTeam?.id?.toString() || '',
      awayTeam: event.game?.awayTeam?.id?.toString() || '',
      gameType: resolvedGameType,
      leagueId: event.game && (event.game as any).league?.id ? (event.game as any).league.id.toString() : '',
      permissionType: event.permissionType ?? 'public',
      tournamentId: event.tournament?.id?.toString() || (event as any).tournamentId?.toString() || '',
      tournamentType: event.tournament?.settings?.tournamentType || undefined,
      tournamentRoundDuration: event.tournament?.settings?.roundDuration || 10,
      tournamentBreakTime: event.tournament?.settings?.breakTime || 2,
      tournamentGameMode: event.tournament?.settings?.gameMode || 'round_robin',
      tournamentNumberOfGroups: event.tournament?.settings?.numberOfGroups || 2,
      tournament: event.tournament,
      teamIds: event.teamIds,
      tournamentMatches: event.tournament?.matches || [],
      pendingTournamentMatches: event.tournament?.matches ? (console.debug("EDIT: ", event.tournament?.matches), fullfillPendingTournamentMatches(event.tournament?.matches)) : [],
      task: event.task ? {
        id: event.task.id,
        isRecurring: event.task.isRecurring,
        recurrenceMode: event.task.recurrenceMode,
        recurrenceRule: event.task.recurrenceRule,
        rotationUsers: event.task.rotationUsers,
        rotationCount: event.task.rotationCount,
        offset: event.task.offset,
      } : undefined
    });
    setEditingEventId(event.id);
    setSelectedEvent(null);
    setEventModalOpen(true);
  };

  // Formular-Änderungen verwalten
  const handleFormChange = useCallback((field: string, value: string | number | boolean | string[]) => {
    setEventFormData(prev => ({
      ...prev,
      [field]: value
    }));
  }, []);

  // Event speichern
  const handleSaveEvent = async () => {
    // Basis-Validierung
    if (!eventFormData.title?.trim()) {
      showAlert('Bitte geben Sie einen Titel ein.', 'warning', 'Titel fehlt');
      return;
    }
    
    if (!eventFormData.date) {
      showAlert('Bitte wählen Sie ein Datum aus.', 'warning', 'Datum fehlt');
      return;
    }
    
    if (!eventFormData.eventType) {
      showAlert('Bitte wählen Sie einen Event-Typ aus.', 'warning', 'Event-Typ fehlt');
      return;
    }

    // Event type flags — single source of truth
    const { isMatchEvent, isTournament, isTask } = getEventTypeFlags(
      eventFormData.eventType, eventFormData.gameType, eventTypesOptions, gameTypesOptions,
    );
    
    if (isMatchEvent && !isTournament) {
      if (!eventFormData.homeTeam) {
        showAlert('Bitte wählen Sie ein Heim-Team aus.', 'warning', 'Heim-Team fehlt');
        return;
      }
      if (!eventFormData.awayTeam) {
        showAlert('Bitte wählen Sie ein Auswärts-Team aus.', 'warning', 'Auswärts-Team fehlt');
        return;
      }
      if (eventFormData.homeTeam === eventFormData.awayTeam) {
        showAlert('Heim-Team und Auswärts-Team können nicht identisch sein.', 'warning', 'Ungültige Team-Auswahl');
        return;
      }
    }

    setEventSaving(true);

    try {
      // Start-DateTime zusammenbauen
      const startDateTime = eventFormData.time 
        ? `${eventFormData.date}T${eventFormData.time}:00`
        : `${eventFormData.date}T00:00:00`;
      
      // End-DateTime zusammenbauen (falls vorhanden)
      let endDateTime = startDateTime;
      if (eventFormData.endDate) {
        endDateTime = eventFormData.endTime 
          ? `${eventFormData.endDate}T${eventFormData.endTime}:00`
          : `${eventFormData.endDate}T23:59:59`;
      }

      const payload: any = {
        title: eventFormData.title,
        startDate: startDateTime,
        endDate: endDateTime,
        eventTypeId: eventFormData.eventType ? parseInt(eventFormData.eventType) : undefined,
        description: eventFormData.description || '',
        locationId: eventFormData.locationId ? parseInt(eventFormData.locationId) : undefined,
        permissionType: eventFormData.permissionType || 'public',
        permissionTeams: eventFormData.permissionTeams?.map(id => parseInt(id)) || [],
        permissionClubs: eventFormData.permissionClubs?.map(id => parseInt(id)) || [],
        permissionUsers: eventFormData.permissionUsers?.map(id => parseInt(id)) || []
      };

      // Match event data
      if (isMatchEvent) {
        // Non-tournament match events: add game (home/away teams)
        if (!isTournament && eventFormData.homeTeam && eventFormData.awayTeam) {
          payload.game = {
            homeTeamId: parseInt(eventFormData.homeTeam),
            awayTeamId: parseInt(eventFormData.awayTeam)
          };
        }

        // GameType & League
        if (eventFormData.gameType) {
          payload.gameTypeId = parseInt(eventFormData.gameType);
        }
        payload.leagueId = eventFormData.leagueId ? parseInt(eventFormData.leagueId) : undefined;
      }

      // Tournament data (same for both "Turnier" and "Spiel + Turnier GameType")
      if (isTournament) {
        const teamIds = new Set<string>();
        (eventFormData.pendingTournamentMatches || []).forEach((m: any) => {
          if (m.homeTeamId) teamIds.add(String(m.homeTeamId));
          if (m.awayTeamId) teamIds.add(String(m.awayTeamId));
        });
        const selectedTeams = tournamentTeams.filter(t => teamIds.has(t.value));
        payload.tournamentType = eventFormData.tournamentType || 'indoor_hall';
        payload.tournamentRoundDuration = eventFormData.tournamentRoundDuration || 10;
        payload.tournamentBreakTime = eventFormData.tournamentBreakTime || 2;
        payload.tournamentGameMode = eventFormData.tournamentGameMode || 'round_robin';
        payload.tournamentNumberOfGroups = eventFormData.tournamentNumberOfGroups || 2;
        payload.pendingTournamentMatches = eventFormData.pendingTournamentMatches;
        payload.teams = selectedTeams.map(t => t.value);
      }

      // Task-spezifische Daten hinzufügen
      if (isTask) {
        var taskRecurrenceRule = JSON.stringify({'freq': eventFormData.taskFreq, 'interval': eventFormData.taskInterval, 'byday': eventFormData.taskByDay, 'bymonthday': eventFormData.taskByMonthDay});
        payload.task = {
          isRecurring: eventFormData.taskIsRecurring || false,
          recurrenceMode: eventFormData.taskRecurrenceMode || 'classic',
          rotationUsers: eventFormData.taskRotationUsers?.map(id => parseInt(id)) || [],
          rotationCount: eventFormData.taskRotationCount || 1,
          recurrenceRule: taskRecurrenceRule,
          offset: eventFormData.taskOffset || 0
        };
        
        // Recurrence-Rule für classic Mode zusammenbauen
        if (eventFormData.taskIsRecurring && eventFormData.taskRecurrenceMode === 'classic') {
          const rule: any = { freq: eventFormData.taskFreq || 'WEEKLY', interval: eventFormData.taskInterval || 1 };
          if (eventFormData.taskFreq === 'WEEKLY') rule.byday = [eventFormData.taskByDay || 'MO'];
          if (eventFormData.taskFreq === 'MONTHLY') rule.bymonthday = eventFormData.taskByMonthDay || 1;
          payload.task.recurrenceRule = JSON.stringify(rule);
        }
      }

      const url = editingEventId ? `/api/calendar/event/${editingEventId}` : '/api/calendar/event';
      const method = editingEventId ? 'PUT' : 'POST';

      const response = await apiRequest(url, {
        method: method as any,
        body: payload
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'Unbekannter Fehler' }));
        console.error(response);
        console.error(errorData);
        throw new Error(errorData.message || `HTTP ${response.status}`);
      }

      await refreshEvents();

      setEventModalOpen(false);
      setEventFormData({
        title: '',
        date: '',
        time: '',
        eventType: '',
        locationId: '',
        description: '',
        permissionType: 'public',
        taskOffset: 0
      });

      showAlert('Event wurde erfolgreich gespeichert!', 'success', 'Erfolgreich gespeichert');
      
    } catch (error: any) {
      console.error('Error saving event:', error);
      showAlert('Fehler beim Speichern des Events: ' + error.message, 'error', 'Speichern fehlgeschlagen');
    } finally {
      setEventSaving(false);
    }
  };

  const handleDeleteEvent = async () => {
    if (!editingEventId) return;

    const isTaskEvent = eventFormData.task && Object.keys(eventFormData.task).length > 0;

    if (isTaskEvent) {
      setTaskDeletionModal({ open: true });
    } else {
      showConfirm(
        'Möchten Sie dieses Event wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.',
        async () => {
          await performDeleteEvent('single');
        },
        'Event löschen'
      );
    }
  };

  const performDeleteEvent = async (deletionMode: 'single' | 'series') => {
    if (!editingEventId) return;
    
    setEventSaving(true);
    try {
      const response = await apiRequest(`/api/calendar/event/${editingEventId}`, {
        method: 'DELETE',
        body: {deletionMode: deletionMode}
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'Unbekannter Fehler' }));
        throw new Error(errorData.message || `HTTP ${response.status}`);
      }

      // Events neu laden
      await refreshEvents();

      setEventModalOpen(false);
      setTaskDeletionModal({ open: false });
      setEventFormData({
        title: '',
        date: '',
        time: '',
        eventType: '',
        locationId: '',
        description: '',
        permissionType: 'public',
        taskOffset: 0
      });

      showAlert('Event wurde erfolgreich gelöscht!', 'success', 'Erfolgreich gelöscht');
      
    } catch (error: any) {
      console.error('Error deleting event:', error);
      showAlert('Fehler beim Löschen des Events: ' + error.message, 'error', 'Löschen fehlgeschlagen');
    } finally {
      setEventSaving(false);
    }
  }

  // Helper-Funktion zum Neu-Laden der Events
  const refreshEvents = async () => {
    const viewType = view === 'month' ? 'month' : view === 'week' ? 'week' : 'day';
    const start = moment(date).startOf(viewType as any).toISOString();
    const end = moment(date).endOf(viewType as any).toISOString();
    const updatedEvents = await apiJson<CalendarEvent[] | { error: string }>(`/api/calendar/events?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`);
    
    let mappedEvents: CalendarEvent[] = [];
    if (Array.isArray(updatedEvents)) {
      mappedEvents = updatedEvents.map((ev: any) => ({
        id: ev.id || 0,
        title: ev.title || 'Unbenannter Termin',
        start: ev.start,
        end: ev.end,
        description: ev.description || '',
        eventType: ev.type || {},  // Backend sendet 'type', wir mappen es zu 'eventType'
        location: ev.location || {},
        game: ev.game || undefined,
        task: ev.task || undefined,
        weatherData: ev.weatherData || undefined
      }));
    }
    setEvents(mappedEvents);
  };

  // Floating Action Button für neues Event
  const handleAddEvent = () => {
    const today = moment().format('YYYY-MM-DD');
    setEventFormData({
      title: '',
      date: today,
      time: '',
      eventType: '',
      locationId: '',
      description: '',
      taskOffset: 0
    });
    setEditingEventId(null);
    setEventModalOpen(true);
  };

  // Custom Event-Styling basierend auf dem Event-Type
  const eventStyleGetter = (event: any) => {
    // Verwende die Farbe des Event-Types, falls vorhanden
    let backgroundColor = event.eventType?.color || theme.palette.primary.main;
    
    return {
      style: {
        backgroundColor,
        borderRadius: '4px',
        opacity: 0.9,
        color: 'white',
        border: '0px',
        display: 'block',
        fontWeight: 'bold',
        fontSize: isMobile ? '0.75rem' : '0.875rem'
      }
    };
  };

  const calendarStyle = useMemo(() => ({
    height: isMobile ? 'calc(100vh - 200px)' : 'calc(100vh - 200px)',
    minHeight: isMobile ? '400px' : '500px',
    backgroundColor: theme.palette.background.paper,
    '& .rbc-calendar': {
      fontFamily: theme.typography.fontFamily,
      fontSize: isMobile ? '0.75rem' : '0.875rem',
    },
    '& .rbc-header': {
      backgroundColor: theme.palette.mode === 'dark' ? theme.palette.grey[800] : theme.palette.grey[100],
      color: theme.palette.text.primary,
      borderColor: theme.palette.divider,
      padding: isMobile ? theme.spacing(0.5) : theme.spacing(1),
      fontSize: isMobile ? '0.75rem' : '0.875rem',
    },
    '& .rbc-month-view, & .rbc-time-view': {
      border: `1px solid ${theme.palette.divider}`,
    },
    '& .rbc-day-bg': {
      backgroundColor: theme.palette.background.default,
      minHeight: isMobile ? '60px' : '80px',
    },
    '& .rbc-today': {
      backgroundColor: theme.palette.mode === 'dark' 
        ? theme.palette.primary.dark + '20' 
        : theme.palette.primary.light + '20',
    },
    '& .rbc-off-range-bg': {
      backgroundColor: theme.palette.mode === 'dark' 
        ? theme.palette.grey[900] 
        : theme.palette.grey[50],
    },
    '& .rbc-event': {
      fontSize: isMobile ? '0.65rem' : '0.75rem',
      padding: isMobile ? '1px 2px' : '2px 4px',
      lineHeight: isMobile ? 1.2 : 1.4,
    },
    '& .rbc-toolbar': {
      marginBottom: theme.spacing(2),
      flexDirection: isMobile ? 'column' : 'row',
      gap: isMobile ? theme.spacing(1) : 0,
      '& .rbc-btn-group': {
        display: isMobile ? 'none' : 'flex', // Hide default buttons on mobile
      },
      '& button': {
        color: theme.palette.text.primary,
        border: `1px solid ${theme.palette.divider}`,
        backgroundColor: theme.palette.background.paper,
        fontSize: isMobile ? '0.75rem' : '0.875rem',
        padding: isMobile ? theme.spacing(0.5) : theme.spacing(1),
        '&:hover': {
          backgroundColor: theme.palette.action.hover,
        },
        '&.rbc-active': {
          backgroundColor: theme.palette.primary.main,
          color: theme.palette.primary.contrastText,
        }
      },
      '& .rbc-toolbar-label': {
        color: theme.palette.text.primary,
        fontWeight: theme.typography.fontWeightMedium,
        fontSize: isMobile ? '0.9rem' : '1rem',
        textAlign: 'center',
        margin: isMobile ? theme.spacing(1, 0) : 0,
      }
    },
    // Mobile-specific overrides
    ...(isMobile && {
      '& .rbc-date-cell': {
        padding: '2px',
        fontSize: '0.7rem',
      },
      '& .rbc-header': {
        minHeight: '32px',
      },
      '& .rbc-month-row': {
        minHeight: '60px',
      },
      '& .rbc-agenda-view': {
        fontSize: '0.8rem',
        '& table': {
          fontSize: '0.8rem',
        },
        '& .rbc-agenda-time-cell': {
          width: '80px',
        },
      },
    }),
  }), [theme, isMobile]);

  // Custom navigation handlers for mobile
  const navigateToToday = () => setDate(new Date());
  const navigateBack = () => {
    const newDate = moment(date);
    if (view === Views.MONTH || view === 'month') {
      newDate.subtract(1, 'month');
    } else if (view === Views.WEEK || view === 'week') {
      newDate.subtract(1, 'week');
    } else {
      newDate.subtract(1, 'day');
    }
    setDate(newDate.toDate());
  };
  const navigateForward = () => {
    const newDate = moment(date);
    if (view === Views.MONTH || view === 'month') {
      newDate.add(1, 'month');
    } else if (view === Views.WEEK || view === 'week') {
      newDate.add(1, 'week');
    } else {
      newDate.add(1, 'day');
    }
    setDate(newDate.toDate());
  };

  const getViewLabel = (view: string) => {
    switch(view) {
      case 'month': return 'Monat';
      case 'week': return 'Woche';
      case 'day': return 'Tag';
      case 'agenda': return 'Liste';
      default: return view;
    }
  };

  // Gefilterte Events basierend auf den aktiven Event-Types
  const filteredEvents = useMemo(() => {
    return events.filter(event => {
      // Events ohne eventType sind immer sichtbar
      if (!event.eventType?.id) return true;
      // Nur Events mit aktiven Event-Types anzeigen
      return activeEventTypeIds.has(event.eventType.id);
    });
  }, [events, activeEventTypeIds]);

  // Toggle-Funktion für Event-Type-Filter
  const toggleEventType = (eventTypeId: number) => {
    setActiveEventTypeIds(prev => {
      const newSet = new Set(prev);
      if (newSet.has(eventTypeId)) {
        newSet.delete(eventTypeId);
      } else {
        newSet.add(eventTypeId);
      }
      return newSet;
    });
  };

  // Memoize mapped props to prevent unnecessary re-renders of EventModal
  // MUST be before early returns (loading/error) - React Hooks rules!
  const eventTypesOptions = useMemo(
    () => eventTypes.entries.map(et => ({ value: et.id.toString(), label: et.name })),
    [eventTypes.entries]
  );
  
  const tournamentTeams = useMemo(
    () => teams.map(team => ({ value: team.id.toString(), label: team.name })),
    [teams]
  );
  
  const gameTypesOptions = useMemo(
    () => gameTypes.entries.map(gt => ({ value: gt.id.toString(), label: gt.name })),
    [gameTypes.entries]
  );
  
  const locationsOptions = useMemo(
    () => locations.map(l => ({ value: l.id.toString(), label: l.name })),
    [locations]
  );

  if (loading) return <Box sx={{ display: 'flex', justifyContent: 'center', py: 6 }}><Typography>Lade Termine...</Typography></Box>;
  if (error) return <Box sx={{ display: 'flex', justifyContent: 'center', py: 6 }}><Typography color="error">{error}</Typography></Box>;

  return (
    <>
      <Box sx={{ width: '100%', height: '100%', p: 3 }}>
        <Box sx={{ p: { xs: 1, sm: 2 } }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
            <Typography variant={isMobile ? "h5" : "h4"} component="h1">
              Kalender
            </Typography>
            
            {/* Event-Type Filter mit Chips */}
            <Box sx={{ display: 'flex', gap: 2, alignItems: 'center', flexWrap: 'wrap' }}>
              <Stack direction="row" spacing={1} sx={{ flexWrap: 'wrap', gap: 1 }}>
                {eventTypes.entries.map(et => (
                  <Chip
                    key={et.id}
                    label={et.name}
                    onClick={() => toggleEventType(et.id)}
                    variant={activeEventTypeIds.has(et.id) ? 'filled' : 'outlined'}
                    sx={{
                      backgroundColor: activeEventTypeIds.has(et.id) ? (et.color || '#1976d2') : 'transparent',
                      color: activeEventTypeIds.has(et.id) ? '#ffffff' : (et.color || '#1976d2'),
                      borderColor: et.color || '#1976d2',
                      fontWeight: 'bold',
                      '&:hover': {
                        backgroundColor: activeEventTypeIds.has(et.id) 
                          ? (et.color || '#1976d2')
                          : `${et.color || '#1976d2'}20`,
                        transform: 'scale(1.05)',
                      },
                      '&:active': {
                        transform: 'scale(0.95)',
                      }
                    }}
                  />
                ))}
              </Stack>
              
              {eventTypes.createAndEditAllowed && (
                <Button
                  variant="contained"
                  startIcon={<AddIcon />}
                  onClick={handleAddEvent}
                  size={isMobile ? "small" : "medium"}
                >
                  {isMobile ? "Neu" : "Neues Event"}
                </Button>
              )}
            </Box>
          </Box>
          
          {/* Mobile Navigation */}
          {isMobile && (
            <Paper elevation={1} sx={{ p: 2, mb: 2 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
              <IconButton onClick={navigateBack} size="small">
                <ArrowBackIcon />
              </IconButton>
              
              <Typography variant="h6" component="div" sx={{ textAlign: 'center', flex: 1 }}>
                {moment(date).format('MMMM YYYY')}
              </Typography>
              
              <IconButton onClick={navigateForward} size="small">
                <ArrowForwardIcon />
              </IconButton>
            </Box>
            
            <Box sx={{ display: 'flex', gap: 1, justifyContent: 'center' }}>
              <IconButton onClick={navigateToToday} size="small" title="Heute">
                <TodayIcon />
              </IconButton>
              
              <ButtonGroup variant="outlined" size="small">
                {availableViews.map((v) => (
                  <Button
                    key={v}
                    variant={view === v ? 'contained' : 'outlined'}
                    onClick={() => setView(v as any)}
                    size="small"
                  >
                    {getViewLabel(v)}
                  </Button>
                ))}
              </ButtonGroup>
            </Box>
          </Paper>
          )}
        </Box>
        
        <Box sx={calendarStyle}>
          <BigCalendar
            localizer={localizer}
            events={filteredEvents}
            startAccessor="start"
            endAccessor="end"
            view={view}
            onView={setView}
            date={date}
            onNavigate={setDate}
            eventPropGetter={eventStyleGetter}
            messages={messages}
            formats={formats}
            culture="de-DE"
            style={{ height: '100%' }}
            views={availableViews}
            step={30}
            showMultiDayTimes
            defaultDate={new Date()}
            onSelectEvent={handleEventClick}
            onSelectSlot={handleDateClick}
            selectable={true}
            // Custom toolbar for desktop, hidden for mobile
            components={{
              toolbar: isMobile ? () => null : undefined,
              agenda: {
                date: ({ label }: { label: string }) => (
                  <span style={{ fontWeight: 'bold', fontSize: '14px' }}>
                    {moment(label).format('dddd, DD.MM.YYYY')}
                  </span>
                ),
                time: ({ event }: { event: any }) => (
                  <span style={{ fontSize: '13px' }}>
                    {(() => {
                      const start = moment(event.start);
                      const end = moment(event.end);
                      const isSameDay = start.format('YYYY-MM-DD') === end.format('YYYY-MM-DD');
                      
                      if (isSameDay) {
                        return `${start.format('HH:mm')} – ${end.format('HH:mm')}`;
                      } else {
                        return `${start.format('HH:mm')} (mehrtägig)`;
                      }
                    })()}
                  </span>
                ),
                event: ({ event }: { event: any }) => (
                  <span style={{ fontWeight: '500' }}>
                    {event.title}
                    {event.eventType?.name && (
                      <span style={{ 
                        marginLeft: '8px', 
                        fontSize: '12px', 
                        color: event.eventType.color || '#666',
                        fontWeight: '600'
                      }}>
                        [{event.eventType.name}]
                      </span>
                    )}
                  </span>
                )
              }
            }}
          />
        </Box>
      </Box>

      {/* Event Details Modal */}
      <EventDetailsModal 
        open={!!selectedEvent} 
        onClose={() => setSelectedEvent(null)} 
        event={selectedEvent ? {
          id: selectedEvent.id,
          title: selectedEvent.title,
          start: selectedEvent.start,
          end: selectedEvent.end,
          description: selectedEvent.description,
          type: selectedEvent.eventType,
          location: selectedEvent.location,
          game: selectedEvent.game,
          task: selectedEvent.task,
          weatherData: selectedEvent.weatherData,
          tournament: selectedEvent?.tournament
        } : null}
        onEdit={() => selectedEvent && handleEditEvent(selectedEvent)}
        showEdit={true}
      />

      {/* Event Create/Edit Modal */}
      <EventModal
        open={eventModalOpen}
        onClose={() => {
          setEventModalOpen(false);
          setEventFormData({
            title: '',
            date: '',
            time: '',
            eventType: '',
            locationId: '',
            description: '',
            permissionType: 'public',
            taskOffset: 0
          });
        }}
        onSave={handleSaveEvent}
        onDelete={editingEventId ? handleDeleteEvent : undefined}
        showDelete={!!editingEventId}
        event={eventFormData as any}
        eventTypes={eventTypesOptions}
        teams={tournamentTeams}
        gameTypes={gameTypesOptions}
        locations={locationsOptions}
        users={users}
        onChange={handleFormChange}
        loading={eventSaving}
      />

      {/* Alert Modal */}
      <AlertModal
        open={alertModal.open}
        onClose={() => setAlertModal({ ...alertModal, open: false })}
        title={alertModal.title}
        message={alertModal.message}
        severity={alertModal.severity}
      />

      {/* Confirmation Modal */}
      <DynamicConfirmationModal
        open={confirmModal.open}
        onClose={() => setConfirmModal({ ...confirmModal, open: false })}
        onConfirm={() => {
          confirmModal.onConfirm();
          setConfirmModal({ ...confirmModal, open: false });
        }}
        title={confirmModal.title}
        message={confirmModal.message}
        confirmText="Löschen"
        cancelText="Abbrechen"
        confirmColor="error"
        loading={eventSaving}
      />

      {/* Task Deletion Modal with 3 buttons */}
      <TaskDeletionModal
        open={taskDeletionModal.open}
        onClose={() => setTaskDeletionModal({ open: false })}
        onDeleteSingle={() => {
          setTaskDeletionModal({ open: false });
          performDeleteEvent('single');
        }}
        onDeleteSeries={() => {
          setTaskDeletionModal({ open: false });
          performDeleteEvent('series');
        }}
        loading={eventSaving}
      />
    </>
  );
}

type CalendarExportProps = {
  setCalendarFabHandler?: (handler: (() => void) | null) => void;
};

export default function Calendar({ setCalendarFabHandler }: CalendarExportProps) {
  return (
    <CalendarErrorBoundary>
      <CalendarInner setCalendarFabHandler={setCalendarFabHandler} />
    </CalendarErrorBoundary>
  );
}
