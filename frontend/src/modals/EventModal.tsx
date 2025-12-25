import React, { useState, useEffect } from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Stepper from '@mui/material/Stepper';
import Step from '@mui/material/Step';
import StepLabel from '@mui/material/StepLabel';
import IconButton from '@mui/material/IconButton';
import CloseIcon from '@mui/icons-material/Close';
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
  const [wizardOpen, setWizardOpen] = useState(false);
  const [wizardStep, setWizardStep] = useState(0);
  const [wizardError, setWizardError] = useState<string | null>(null);
  const getUserLabel = (user: User) => {
    if (user.fullName) return user.fullName;
    if (user.firstName && user.lastName) return `${user.firstName} ${user.lastName}`;
    if (user.firstName) return user.firstName;
    if (user.lastName) return user.lastName;
    return `User #${user.id}`;
  };
  
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

  return (
    <>
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
            <Button onClick={() => setWizardOpen(true)} color="info" variant="outlined" style={{ marginLeft: 12 }}>
              Wizard starten
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

      {/* Wizard Dialog */}
      <Dialog open={wizardOpen} onClose={() => setWizardOpen(false)} maxWidth="md" fullWidth>
        <DialogTitle>
          Event Wizard
          <IconButton
            aria-label="close"
            onClick={() => setWizardOpen(false)}
            sx={{ position: 'absolute', right: 8, top: 8 }}
          >
            <CloseIcon />
          </IconButton>
        </DialogTitle>
        <DialogContent>
          <Stepper activeStep={wizardStep} alternativeLabel>
            <Step key="Basisdaten"><StepLabel>Basisdaten</StepLabel></Step>
            <Step key="Details"><StepLabel>Details</StepLabel></Step>
            {!isGameEvent && !isTaskEvent && <Step key="Berechtigungen"><StepLabel>Berechtigungen</StepLabel></Step>}
            <Step key="Beschreibung"><StepLabel>Beschreibung</StepLabel></Step>
            <Step key="Zusammenfassung"><StepLabel>Zusammenfassung</StepLabel></Step>
          </Stepper>
          <div style={{ marginTop: 32 }}>
            {wizardError && (
              <div style={{ color: 'red', marginBottom: 12 }}>{wizardError}</div>
            )}
            {wizardStep === 0 && (
              <>
                <TextField
                  label="Titel *"
                  value={event.title || ''}
                  onChange={e => onChange('title', e.target.value)}
                  fullWidth
                  margin="normal"
                  required
                />
                <FormControl fullWidth margin="normal" required>
                  <InputLabel id="wizard-event-type-label">Event-Typ *</InputLabel>
                  <Select
                    labelId="wizard-event-type-label"
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
              </>
            )}
            {wizardStep === 1 && (
              <>
                {/* Details: adapt to event type */}
                {isGameEvent && (
                  <>
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
                    <FormControl fullWidth margin="normal" required>
                      <InputLabel id="wizard-home-team-label">Heim-Team *</InputLabel>
                      <Select
                        labelId="wizard-home-team-label"
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
                      <InputLabel id="wizard-away-team-label">Auswärts-Team *</InputLabel>
                      <Select
                        labelId="wizard-away-team-label"
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
                )}
              </>
            )}
            {wizardStep === (isGameEvent || isTaskEvent ? 2 : 2) && !isGameEvent && !isTaskEvent && (
              <>
                {/* Berechtigungen */}
                <FormControl fullWidth margin="normal">
                  <InputLabel id="wizard-permission-type-label">Sichtbarkeit</InputLabel>
                  <Select
                    labelId="wizard-permission-type-label"
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
            {wizardStep === (isGameEvent || isTaskEvent ? 2 : 3) && (
              <>
                <TextField
                  label="Beschreibung"
                  value={event.description || ''}
                  onChange={e => onChange('description', e.target.value)}
                  fullWidth
                  margin="normal"
                  multiline
                  rows={4}
                />
              </>
            )}
            {wizardStep === (isGameEvent || isTaskEvent ? 3 : 4) && (
              <>
                <h3>Zusammenfassung</h3>
                <div style={{ marginBottom: 16 }}>
                  <strong>Titel:</strong> {event.title}<br />
                  <strong>Typ:</strong> {eventTypes.find(et => et.value === event.eventType)?.label}<br />
                  <strong>Start:</strong> {event.date} {event.time}<br />
                  <strong>Ende:</strong> {event.endDate} {event.endTime}<br />
                  <strong>Ort:</strong> {locations.find(l => l.value === event.locationId)?.label}<br />
                  {isGameEvent && <>
                    <strong>Heim-Team:</strong> {teams.find(t => t.value === event.homeTeam)?.label}<br />
                    <strong>Auswärts-Team:</strong> {teams.find(t => t.value === event.awayTeam)?.label}<br />
                    {event.gameType && <><strong>Spiel-Typ:</strong> {gameTypes.find(gt => gt.value === event.gameType)?.label}<br /></>}
                    {event.leagueId && <><strong>Liga:</strong> {leagues.find(l => l.value === event.leagueId)?.label}<br /></>}
                  </>}
                  {isTaskEvent && <>
                    <strong>Benutzer-Rotation:</strong> {users.filter(u => (event.taskRotationUsers || []).includes(u.id.toString())).map(getUserLabel).join(', ')}<br />
                    <strong>Personen pro Aufgabe:</strong> {event.taskRotationCount}<br />
                    <strong>Wiederkehrend:</strong> {event.taskIsRecurring ? 'Ja' : 'Nein'}<br />
                    {event.taskIsRecurring && <>
                      <strong>Modus:</strong> {event.taskRecurrenceMode === 'per_match' ? 'Pro Spiel' : 'Regelmäßig'}<br />
                      {event.taskRecurrenceMode === 'classic' && <>
                        <strong>Frequenz:</strong> {event.taskFreq}<br />
                        <strong>Intervall:</strong> {event.taskInterval}<br />
                        {event.taskFreq === 'WEEKLY' && <><strong>Wochentag:</strong> {event.taskByDay}<br /></>}
                        {event.taskFreq === 'MONTHLY' && <><strong>Tag des Monats:</strong> {event.taskByMonthDay}<br /></>}
                      </>}
                      {event.taskRecurrenceMode === 'per_match' && <>
                        <strong>Offset zum Spieltag:</strong> {event.taskOffset}<br />
                      </>}
                    </>}
                  </>}
                  {!isGameEvent && !isTaskEvent && <>
                    <strong>Berechtigung:</strong> {event.permissionType}<br />
                  </>}
                  <strong>Beschreibung:</strong> {event.description}<br />
                </div>
              </>
            )}
          </div>
        </DialogContent>
        <DialogActions>
          <Button
            onClick={() => {
              setWizardError(null);
              setWizardStep(prev => Math.max(prev - 1, 0));
            }}
            disabled={wizardStep === 0}
          >Zurück</Button>
          {(wizardStep < (isGameEvent || isTaskEvent ? 3 : 4)) ? (
            <Button
              onClick={() => {
                setWizardError(null);
                // Validierung je nach Step und Event-Typ
                if (wizardStep === 0) {
                  if (!event.title || !event.eventType || !event.date) {
                    setWizardError('Bitte Titel, Event-Typ und Start-Datum angeben!');
                    return;
                  }
                }
                if (wizardStep === 1) {
                  if (isGameEvent) {
                    if (!event.homeTeam || !event.awayTeam) {
                      setWizardError('Bitte Heim- und Auswärts-Team angeben!');
                      return;
                    }
                    if (!event.locationId) {
                      setWizardError('Bitte Austragungsort auswählen!');
                    }
                  }
                  if (isTaskEvent) {
                    if (!event.taskRotationUsers || event.taskRotationUsers.length === 0) {
                      setWizardError('Bitte mindestens einen Benutzer für die Rotation auswählen!');
                      return;
                    }
                    if (!event.taskRotationCount || event.taskRotationCount < 1) {
                      setWizardError('Bitte eine gültige Anzahl Personen pro Aufgabe angeben!');
                      return;
                    }
                    if (event.taskIsRecurring) {
                      if (!event.taskRecurrenceMode) {
                        setWizardError('Bitte Wiederkehr-Modus wählen!');
                        return;
                      }
                      if (event.taskRecurrenceMode === 'classic') {
                        if (!event.taskFreq || !event.taskInterval) {
                          setWizardError('Bitte Frequenz und Intervall angeben!');
                          return;
                        }
                      }
                    }
                  }
                }
                if (wizardStep === 2 && !isGameEvent && !isTaskEvent) {
                  if (!event.permissionType) {
                    setWizardError('Bitte eine Sichtbarkeit wählen!');
                    return;
                  }
                }
                setWizardStep(prev => prev + 1);
              }}
              color="primary"
            >Weiter</Button>
          ) : (
            <Button
              onClick={() => { setWizardOpen(false); setWizardStep(0); setWizardError(null); }}
              color="success"
            >Fertig</Button>
          )}
        </DialogActions>
      </Dialog>
    </>
  );
}
