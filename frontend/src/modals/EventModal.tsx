import React, { useState, useEffect } from 'react';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import InputLabel from '@mui/material/InputLabel';
import FormControl from '@mui/material/FormControl';
import Autocomplete from '@mui/material/Autocomplete';
import FormControlLabel from '@mui/material/FormControlLabel';
import Checkbox from '@mui/material/Checkbox';
import BaseModal from './BaseModal';
import { fetchLeagues } from '../services/leagues';
import { League } from '../types/league';

interface User {
  id: string | number;
  fullName?: string;
  firstName?: string;
  lastName?: string;
}

interface EventData {
  title?: string;
  date?: string;
  time?: string;
  endDate?: string;
  endTime?: string;
  eventType?: string;
  locationId?: string;
  description?: string;
  homeTeam?: string;
  awayTeam?: string;
  gameType?: string;
  leagueId?: string;
  permissionType?: string;
  permissionTeams?: string[];
  permissionClubs?: string[];
  permissionUsers?: string[];
  // Task-bezogene Felder
  task?: {
    id?: number;
    isRecurring?: boolean;
    recurrenceMode?: string;
    recurrenceRule?: string | null;
    rotationUsers?: { id: number; fullName: string }[];
    rotationCount?: number;
    offset?: number;
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
}

interface EventModalProps {
  open: boolean;
  onClose: () => void;
  onSave: () => void;
  onDelete?: () => void;
  showDelete?: boolean;
  event?: EventData;
  eventTypes: { value: string; label: string }[];
  teams?: { value: string; label: string }[];
  gameTypes?: { value: string; label: string }[];
  locations: { value: string; label: string }[];
  users?: User[];
  onChange: (field: string, value: string | number | boolean | string[]) => void;
  loading?: boolean;
}
export const EventModal: React.FC<EventModalProps> = ({  open,
  onClose,
  onSave,
  onDelete,
  showDelete = false,
  event = {},
  eventTypes,
  teams = [],
  gameTypes = [],
  locations,
  users = [],
  onChange,
  loading = false,
}) => {
  const [leagues, setLeagues] = useState<{ value: string; label: string }[]>([]);
  const [taskDataLoaded, setTaskDataLoaded] = useState(false);
  
  useEffect(() => {
    if (open) {
      fetchLeagues().then(ls => {
        setLeagues(ls.map(l => ({ value: String(l.id), label: l.name })));
      });
      setTaskDataLoaded(false);
    }
  }, [open]);
  // Prüfen ob es sich um ein Spiel-Event handelt
  const selectedEventType = eventTypes.find(et => et.value === event.eventType);
  const isGameEvent = selectedEventType?.label?.toLowerCase().includes('spiel') 
    || selectedEventType?.label?.toLowerCase() === 'spiel' 
    || false;
  
  // Prüfen ob es sich um ein Task-Event handelt
  const isTaskEvent = selectedEventType?.label?.toLowerCase().includes('aufgabe')
    || selectedEventType?.label?.toLowerCase() === 'aufgabe'
    || false;

  // Populate task fields from event.task when event is loaded
  useEffect(() => {
    if (event && event.task && isTaskEvent && !taskDataLoaded) {
      const { task } = event;
      
      onChange('taskIsRecurring', task.isRecurring === true);
      onChange('taskRecurrenceMode', task.recurrenceMode || 'classic');
      onChange('taskRotationCount', task.rotationCount || 1);
      onChange('taskOffset', task.offset || 0);
      
      // Parse recurrence rule if present
      if (task.recurrenceRule) {
        try {
          const rule = JSON.parse(task.recurrenceRule);
          onChange('taskFreq', rule.freq || 'WEEKLY');
          onChange('taskInterval', rule.interval || 1);
          if (rule.byday && rule.byday.length > 0) {
            onChange('taskByDay', rule.byday[0]);
          }
          if (rule.bymonthday) {
            onChange('taskByMonthDay', rule.bymonthday);
          }
        } catch (e) {
          console.error('Failed to parse recurrence rule:', e);
          onChange('taskFreq', 'WEEKLY');
          onChange('taskInterval', 1);
          onChange('taskByDay', 'MO');
        }
      } else {
        // Set defaults
        onChange('taskFreq', 'WEEKLY');
        onChange('taskInterval', 1);
        onChange('taskByDay', 'MO');
      }
      
      // Set rotation users from task
      if (task.rotationUsers && Array.isArray(task.rotationUsers)) {
        onChange('taskRotationUsers', task.rotationUsers.map((u: any) => String(u.id)));
      }
      
      setTaskDataLoaded(true);
    } else if (!event?.task) {
      setTaskDataLoaded(false);
    }
  }, [event?.task, isTaskEvent, onChange]);

  // Hilfsfunktion um Benutzernamen zu formatieren
  const getUserLabel = (user: User) => {
    if (user.fullName) return user.fullName;
    if (user.firstName && user.lastName) return `${user.firstName} ${user.lastName}`;
    if (user.firstName) return user.firstName;
    if (user.lastName) return user.lastName;
    return `User #${user.id}`;
  };

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      title="Event verwalten"
      maxWidth="lg"
      actions={
        <>
          <Button onClick={onClose} color="secondary" variant="outlined" disabled={loading}>
            Abbrechen
          </Button>
          {showDelete && onDelete && (
            <Button onClick={onDelete} color="error" variant="outlined" disabled={loading}>
              Löschen
            </Button>
          )}
          <Button onClick={onSave} color="primary" variant="contained" disabled={loading}>
            {loading ? 'Wird gespeichert...' : 'Speichern'}
          </Button>
        </>
      }
    >
      <div style={{ display: 'flex', gap: 24 }}>
        <div style={{ flex: 1 }}>
          <TextField
            label="Titel *"
            value={event.title || ''}
            onChange={e => onChange('title', e.target.value)}
            fullWidth
            margin="normal"
            required
          />
          <TextField
            label="Datum *"
            type="date"
            value={event.date || ''}
            onChange={e => onChange('date', e.target.value)}
            fullWidth
            margin="normal"
            required
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            label="Uhrzeit"
            type="time"
            value={event.time || ''}
            onChange={e => onChange('time', e.target.value)}
            fullWidth
            margin="normal"
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            label="End-Datum"
            type="date"
            value={event.endDate || ''}
            onChange={e => onChange('endDate', e.target.value)}
            fullWidth
            margin="normal"
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            label="End-Uhrzeit"
            type="time"
            value={event.endTime || ''}
            onChange={e => onChange('endTime', e.target.value)}
            fullWidth
            margin="normal"
            InputLabelProps={{ shrink: true }}
          />
          <FormControl fullWidth margin="normal" required>
            <InputLabel id="event-type-label">Event-Typ *</InputLabel>
            <Select
              labelId="event-type-label"
              value={event.eventType || ''}
              label="Event-Typ *"
              onChange={e => onChange('eventType', e.target.value as string)}
            >
              <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
              {eventTypes.map(opt => (
                <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
              ))}
            </Select>
          </FormControl>
        </div>
        <div style={{ flex: 1 }}>
          <Autocomplete
            options={locations}
            getOptionLabel={(option) => option.label}
            value={locations.find(l => l.value === event.locationId) || null}
            onChange={(_, newValue) => onChange('locationId', newValue?.value || '')}
            renderInput={(params) => (
              <TextField
                {...params}
                label="Ort"
                placeholder="Ort suchen..."
                fullWidth
                margin="normal"
              />
            )}
            filterOptions={(options, { inputValue }) => {
              if (inputValue.length < 2) return [];
              return options.filter(option =>
                option.label.toLowerCase().includes(inputValue.toLowerCase())
              );
            }}
            noOptionsText="Keine Orte gefunden (mindestens 2 Zeichen eingeben)"
          />
          {/* Spiel-spezifische Felder */}
          {isGameEvent && (
            <>
              <FormControl fullWidth margin="normal" required>
                <InputLabel id="home-team-label">Heim-Team *</InputLabel>
                <Select
                  labelId="home-team-label"
                  value={event.homeTeam || ''}
                  label="Heim-Team *"
                  onChange={e => onChange('homeTeam', e.target.value as string)}
                >
                  <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
                  {teams.map(opt => (
                    <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
                  ))}
                </Select>
              </FormControl>
              <FormControl fullWidth margin="normal" required>
                <InputLabel id="away-team-label">Auswärts-Team *</InputLabel>
                <Select
                  labelId="away-team-label"
                  value={event.awayTeam || ''}
                  label="Auswärts-Team *"
                  onChange={e => onChange('awayTeam', e.target.value as string)}
                >
                  <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
                  {teams.map(opt => (
                    <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
                  ))}
                </Select>
              </FormControl>
              {gameTypes.length > 0 && (
                <FormControl fullWidth margin="normal">
                  <InputLabel id="game-type-label">Spiel-Typ</InputLabel>
                  <Select
                    labelId="game-type-label"
                    value={event.gameType || ''}
                    label="Spiel-Typ"
                    onChange={e => onChange('gameType', e.target.value as string)}
                  >
                    <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
                    {gameTypes.map(opt => (
                      <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
              )}
              {leagues.length > 0 && (
                <FormControl fullWidth margin="normal">
                  <InputLabel id="league-label">Liga</InputLabel>
                  <Select
                    labelId="league-label"
                    value={event.leagueId || ''}
                    label="Liga"
                    onChange={e => onChange('leagueId', e.target.value as string)}
                  >
                    <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
                    {leagues.map(opt => (
                      <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
              )}
            </>
          )}
          {/* Aufgaben-spezifische Felder */}
          {isTaskEvent && (
            <>
              <Autocomplete
                multiple
                options={users}
                getOptionLabel={getUserLabel}
                value={users.filter(u => (event.taskRotationUsers || []).includes(u.id.toString()))}
                onChange={(_, val) => onChange('taskRotationUsers', val.map(v => v.id.toString()))}
                renderInput={params => <TextField {...params} label="Benutzer-Rotation" margin="normal" />}
                sx={{ mt: 2, mb: 2 }}
              />
              {users.length === 0 && (
                <div style={{ color: 'orange', margin: '8px 0 16px 0' }}>Keine Benutzer zur Auswahl vorhanden.</div>
              )}
              <TextField
                label="Personen pro Aufgabe gleichzeitig"
                type="number"
                value={event.taskRotationCount || 1}
                onChange={e => onChange('taskRotationCount', parseInt(e.target.value) || 1)}
                fullWidth
                margin="normal"
                inputProps={{ min: 1, max: users.length || 99 }}
              />
              <FormControlLabel
                control={<Checkbox checked={event.taskIsRecurring || false} onChange={e => onChange('taskIsRecurring', e.target.checked)} />}
                label="Wiederkehrend"
              />
              {event.taskIsRecurring && (
                <>
                  <TextField
                    select
                    label="Wiederkehr-Modus"
                    value={event.taskRecurrenceMode || 'classic'}
                    onChange={e => onChange('taskRecurrenceMode', e.target.value)}
                    fullWidth
                    margin="normal"
                    SelectProps={{ native: true }}
                  >
                    <option value="classic">Regelmäßig (z.B. wöchentlich)</option>
                    <option value="per_match">Pro Spiel (laut Spielplan)</option>
                  </TextField>
                  {(event.taskRecurrenceMode === 'classic' || !event.taskRecurrenceMode) && (
                    <>
                      <TextField
                        select
                        label="Frequenz"
                        value={event.taskFreq || 'WEEKLY'}
                        onChange={e => onChange('taskFreq', e.target.value)}
                        fullWidth
                        margin="normal"
                        SelectProps={{ native: true }}
                      >
                        <option value="DAILY">Täglich</option>
                        <option value="WEEKLY">Wöchentlich</option>
                        <option value="MONTHLY">Monatlich</option>
                      </TextField>
                      <TextField
                        label="Intervall (z.B. alle 2 Wochen)"
                        type="number"
                        value={event.taskInterval || 1}
                        onChange={e => onChange('taskInterval', parseInt(e.target.value) || 1)}
                        fullWidth
                        margin="normal"
                        inputProps={{ min: 1 }}
                      />
                      {((event.taskFreq || 'WEEKLY') === 'WEEKLY' || ((event.taskFreq || 'WEEKLY') === 'DAILY' && (event.taskInterval || 1) > 7)) && (
                        <TextField
                          select
                          label="Wochentag"
                          value={event.taskByDay || 'MO'}
                          onChange={e => onChange('taskByDay', e.target.value)}
                          fullWidth
                          margin="normal"
                          SelectProps={{ native: true }}
                        >
                          <option value="MO">Montag</option>
                          <option value="TU">Dienstag</option>
                          <option value="WE">Mittwoch</option>
                          <option value="TH">Donnerstag</option>
                          <option value="FR">Freitag</option>
                          <option value="SA">Samstag</option>
                          <option value="SU">Sonntag</option>
                        </TextField>
                      )}
                      {(event.taskFreq || 'WEEKLY') === 'MONTHLY' && (
                        <TextField
                          label="Tag des Monats"
                          type="number"
                          value={event.taskByMonthDay || 1}
                          onChange={e => onChange('taskByMonthDay', parseInt(e.target.value) || 1)}
                          fullWidth
                          margin="normal"
                          inputProps={{ min: 1, max: 31 }}
                        />
                      )}
                    </>
                  )}
                  {event.taskRecurrenceMode === 'per_match' && (
                    <>
                      <p>Die Aufgabe wird automatisch jedem Spiel zugeordnet.</p>
                      <TextField
                        label="Beginn der Aufgabe, relativ zum Spieltag (z.B. 1 = am Spieltag, 2 = einen Tag nach dem Spieltag, negativ = vor dem Spieltag)"
                        type="number"
                        value={event.taskOffset || 0}
                        onChange={e => onChange('taskOffset', parseInt(e.target.value) || 0)}
                        fullWidth
                        margin="normal"
                        inputProps={{ min: -365, max: 365 }}
                      />
                    </>
                  )}
                </>
              )}
            </>
          )}
          
          {!isGameEvent && !isTaskEvent && (
            <>
              <FormControl fullWidth margin="normal">
                <InputLabel id="permission-type-label">Sichtbarkeit</InputLabel>
                <Select
                  labelId="permission-type-label"
                  value={event.permissionType || 'public'}
                  label="Sichtbarkeit"
                  onChange={e => onChange('permissionType', e.target.value as string)}
                >
                  <MenuItem value="public">Öffentlich</MenuItem>
                  <MenuItem value="club">Spezifische Clubs</MenuItem>
                  <MenuItem value="team">Spezifische Teams</MenuItem>
                  <MenuItem value="user">Spezifische Nutzer</MenuItem>
                </Select>
              </FormControl>

              {event.permissionType === 'team' && (
                <Autocomplete
                  multiple
                  options={teams}
                  getOptionLabel={(option) => option.label}
                  value={teams.filter(t => event.permissionTeams?.includes(t.value))}
                  onChange={(_, newValue) => onChange('permissionTeams', newValue.map(t => t.value))}
                  renderInput={(params) => (
                    <TextField
                      {...params}
                      label="Teams auswählen"
                      margin="normal"
                    />
                  )}
                />
              )}

              {event.permissionType === 'club' && (
                <Autocomplete
                  multiple
                  options={teams.map(t => ({ value: t.value, label: `Club ${t.label}` }))}
                  getOptionLabel={(option) => option.label}
                  value={teams
                    .filter(t => event.permissionClubs?.includes(t.value))
                    .map(t => ({ value: t.value, label: `Club ${t.label}` }))}
                  onChange={(_, newValue) => onChange('permissionClubs', newValue.map(c => c.value))}
                  renderInput={(params) => (
                    <TextField
                      {...params}
                      label="Clubs auswählen"
                      margin="normal"
                    />
                  )}
                />
              )}

              {event.permissionType === 'user' && (
                <Autocomplete
                  multiple
                  options={users}
                  getOptionLabel={(option) => option.fullName || `${option.firstName} ${option.lastName}` || String(option.id)}
                  value={users.filter(u => event.permissionUsers?.includes(String(u.id)))}
                  onChange={(_, newValue) => onChange('permissionUsers', newValue.map(u => String(u.id)))}
                  renderInput={(params) => (
                    <TextField
                      {...params}
                      label="Nutzer auswählen"
                      margin="normal"
                    />
                  )}
                />
              )}
            </>
          )}
          
          <TextField
            label="Beschreibung"
            value={event.description || ''}
            onChange={e => onChange('description', e.target.value)}
            fullWidth
            margin="normal"
            multiline
            rows={4}
          />
        </div>
      </div>
    </BaseModal>
  );
};
