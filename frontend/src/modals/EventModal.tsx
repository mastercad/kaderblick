import React, { useState, useEffect, useCallback, useMemo } from 'react';
import {
  Autocomplete,
  Box,
  Button,
  Stepper,
  Step,
  StepLabel,
  Alert,
  TextField,
  Typography,
  useTheme,
  useMediaQuery,
} from '@mui/material';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import ArrowForwardIcon from '@mui/icons-material/ArrowForward';
import SaveIcon from '@mui/icons-material/Save';
import DeleteOutlineIcon from '@mui/icons-material/DeleteOutline';
import BaseModal from './BaseModal';
import ImportMatchesDialog from './ImportMatchesDialog';
import ManualMatchesEditor from './ManualMatchesEditor';
import TournamentMatchGeneratorDialog from './TournamentMatchGeneratorDialog';
import { EventBaseForm } from '../components/EventModal/EventBaseForm';
import { GameEventFields } from '../components/EventModal/GameEventFields';
import { TaskEventFields } from '../components/EventModal/TaskEventFields';
import { TrainingEventFields } from '../components/EventModal/TrainingEventFields';
import { PermissionFields } from '../components/EventModal/PermissionFields';
import {
  TournamentConfig,
  TournamentMatchesManagement,
  TournamentSelection,
} from '../components/EventModal/TournamentFields';
import { WizardStep2Tournament } from '../components/EventModal/WizardSteps';
import { useTournamentMatches, useLeagues, useReloadTournamentMatches } from '../hooks/useEventData';
import { useEventTypeFlags } from '../hooks/useEventTypeFlags';
import { EventData, SelectOption, User } from '../types/event';
import { apiRequest } from '../utils/api';

// ─── Step keys ───────────────────────────────────────────────────────────────
const STEP_BASE        = 'base';
const STEP_DETAILS     = 'details';
const STEP_MATCHES     = 'matches';
const STEP_PERMISSIONS = 'permissions';
const STEP_DESCRIPTION = 'description';

type WizardStep = { key: string; label: string };

// ─── Props ────────────────────────────────────────────────────────────────────
interface EventModalProps {
  open: boolean;
  onClose: () => void;
  onSave: (formData: EventData) => void;
  onDelete?: () => void;
  showDelete?: boolean;
  event: EventData;
  eventTypes: SelectOption[];
  teams?: SelectOption[];
  /** All teams, bypassing user-assignment filter — used for match & tournament opponent selection. */
  allTeams?: SelectOption[];
  gameTypes?: SelectOption[];
  locations: SelectOption[];
  tournaments?: SelectOption[];
  users?: User[];
  loading?: boolean;
  onChange: (field: string, value: any) => void;
}

// ─── Reusable location autocomplete ──────────────────────────────────────────
const LocationField: React.FC<{
  locations: SelectOption[];
  value: string | undefined;
  onChange: (value: string) => void;
}> = ({ locations, value, onChange }) => (
  <Autocomplete
    options={locations}
    getOptionLabel={(option) => option.label}
    value={locations.find(l => l.value === value) || null}
    onChange={(_, newValue) => onChange(newValue?.value || '')}
    filterOptions={(options, { inputValue }) => {
      if (inputValue.length < 2) return [];
      return options.filter(opt =>
        opt.label.toLowerCase().includes(inputValue.toLowerCase())
      );
    }}
    noOptionsText="Keine Orte gefunden (mindestens 2 Zeichen eingeben)"
    renderInput={(params) => (
      <TextField
        {...params}
        label="Ort"
        placeholder="Ort suchen..."
        fullWidth
        margin="normal"
      />
    )}
  />
);

// ─── Component ────────────────────────────────────────────────────────────────
export const EventModal: React.FC<EventModalProps> = ({
  open,
  onClose,
  onSave,
  onDelete,
  showDelete = false,
  event,
  eventTypes,
  teams = [],
  allTeams = [],
  gameTypes = [],
  locations,
  tournaments = [],
  users = [],
  loading = false,
  onChange,
}) => {
  // For match/tournament events every team must be selectable as an opponent.
  // For training/permission fields only the user's own assigned teams are relevant.
  const matchTeams = allTeams.length > 0 ? allTeams : teams;
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));

  const { tournamentMatches, setTournamentMatches } = useTournamentMatches(event.tournamentId, open);
  const leagues = useLeagues(open);
  const reloadMatches = useReloadTournamentMatches();

  const [currentStep, setCurrentStep]               = useState(0);
  const [stepError, setStepError]                   = useState<string | null>(null);
  const [importOpen, setImportOpen]                 = useState(false);
  const [manualOpen, setManualOpen]                 = useState(false);
  const [generatorOpen, setGeneratorOpen]           = useState(false);
  const [editingMatchId, setEditingMatchId]         = useState<string | number | null>(null);
  const [editingMatchDraft, setEditingMatchDraft]   = useState<any>(null);

  // ── Fetch tournament settings ──────────────────────────────────────────────
  useEffect(() => {
    const fetchTournamentSettings = async () => {
      if (!open || !event.tournamentId) return;
      try {
        const res = await apiRequest(`/api/tournaments/${event.tournamentId}`);
        if (res.ok) {
          const data = await res.json();
          if (data.settings) {
            if (data.settings.type)           onChange('tournamentType',          data.settings.type);
            if (data.settings.roundDuration)  onChange('tournamentRoundDuration', data.settings.roundDuration);
            if (data.settings.breakTime)      onChange('tournamentBreakTime',     data.settings.breakTime);
            if (data.settings.gameMode)       onChange('tournamentGameMode',      data.settings.gameMode);
            if (data.settings.numberOfGroups) onChange('tournamentNumberOfGroups',data.settings.numberOfGroups);
          }
        }
      } catch { /* ignore */ }
    };
    fetchTournamentSettings();
  }, [open, event.tournamentId]); // eslint-disable-line react-hooks/exhaustive-deps

  // ── Reset wizard state on open ────────────────────────────────────────────
  useEffect(() => {
    if (open) {
      setCurrentStep(0);
      setStepError(null);
      setEditingMatchId(null);
      setEditingMatchDraft(null);
    }
  }, [open]);

  // ── Populate tournamentMatches from all available sources ─────────────────
  useEffect(() => {
    if (!open) return;

    if (event?.pendingTournamentMatches && event.pendingTournamentMatches.length > 0) {
      setTournamentMatches(
        event.pendingTournamentMatches.map((match: any, idx: number) => ({
          id: match.id || `draft-${idx}`,
          ...match,
          homeTeamName: teams.find(t => String(t.value) === String(match.homeTeamId))?.label || match.homeTeamName || '',
          awayTeamName: teams.find(t => String(t.value) === String(match.awayTeamId))?.label || match.awayTeamName || '',
        }))
      );
      return;
    }

    if (event?.tournament?.matches && event.tournament.matches.length > 0) {
      setTournamentMatches(
        event.tournament.matches.map((match: any) => ({
          ...match,
          id: match.id || `embedded-${match.round}-${match.slot}`,
          homeTeamName: teams.find(t => String(t.value) === String(match.homeTeamId))?.label || match.homeTeamName || '',
          awayTeamName: teams.find(t => String(t.value) === String(match.awayTeamId))?.label || match.awayTeamName || '',
        }))
      );
    }
  }, [open, event?.pendingTournamentMatches?.length, event?.tournament?.matches?.length, teams]); // eslint-disable-line react-hooks/exhaustive-deps

  // ── Event type flags ──────────────────────────────────────────────────────
  const { isMatchEvent, isTournament, isTournamentEventType, isTask, isTraining, isGenericEvent } =
    useEventTypeFlags(event.eventType, event.gameType, eventTypes, gameTypes);

  const handleChange = useCallback(
    (field: string, value: any) => { if (typeof onChange === 'function') onChange(field, value); },
    [onChange],
  );

  // ── Auto-fill gameType for tournaments ────────────────────────────────────
  useEffect(() => {
    if (open && isTournament && !event.gameType && gameTypes.length > 0) {
      const gt = gameTypes.find(g => g.label.toLowerCase().includes('turnier'));
      if (gt) handleChange('gameType', gt.value);
    }
  }, [open, isTournament, event.gameType, gameTypes, handleChange]);

  // ── Step computation (reactive on event type) ─────────────────────────────
  const steps: WizardStep[] = useMemo(() => {
    const s: WizardStep[] = [{ key: STEP_BASE, label: 'Basisdaten' }];

    if (isMatchEvent || isTournament) {
      s.push({ key: STEP_DETAILS, label: 'Spieldetails' });
      if (isTournament) s.push({ key: STEP_MATCHES, label: 'Begegnungen' });
    } else if (isTask) {
      s.push({ key: STEP_DETAILS, label: 'Aufgabe' });
    } else if (isTraining) {
      s.push({ key: STEP_DETAILS, label: 'Training' });
    } else if (isGenericEvent) {
      s.push({ key: STEP_PERMISSIONS, label: 'Berechtigungen' });
    } else {
      // No type selected yet — generic placeholder
      s.push({ key: STEP_DETAILS, label: 'Details' });
    }

    s.push({ key: STEP_DESCRIPTION, label: 'Beschreibung' });
    return s;
  }, [isMatchEvent, isTournament, isTask, isTraining, isGenericEvent]);

  const isLastStep     = currentStep === steps.length - 1;
  const currentStepKey = steps[currentStep]?.key ?? STEP_BASE;

  // Clamp currentStep when steps array shrinks (event type changed mid-wizard)
  useEffect(() => {
    setCurrentStep(prev => Math.min(prev, steps.length - 1));
  }, [steps.length]);

  // ── Validation ────────────────────────────────────────────────────────────
  const validateCurrentStep = useCallback((): boolean => {
    setStepError(null);

    if (currentStepKey === STEP_BASE) {
      if (!event.title || !event.eventType || !event.date) {
        setStepError('Bitte Titel, Event-Typ und Start-Datum angeben!');
        return false;
      }
    }

    if (currentStepKey === STEP_DETAILS) {
      if (isMatchEvent && !isTournament) {
        if (!event.homeTeam || !event.awayTeam) {
          setStepError('Bitte Heim- und Auswärts-Team angeben!');
          return false;
        }
        if (!event.locationId) {
          setStepError('Bitte Austragungsort auswählen!');
          return false;
        }
      }
      if (isTask) {
        if (!event.taskRotationUsers || event.taskRotationUsers.length === 0) {
          setStepError('Bitte mindestens einen Benutzer für die Rotation auswählen!');
          return false;
        }
        if (!event.taskRotationCount || event.taskRotationCount < 1) {
          setStepError('Bitte eine gültige Anzahl Personen pro Aufgabe angeben!');
          return false;
        }
        if (event.taskIsRecurring) {
          if (!event.taskRecurrenceMode) {
            setStepError('Bitte Wiederkehr-Modus wählen!');
            return false;
          }
          if (event.taskRecurrenceMode === 'classic' && (!event.taskFreq || !event.taskInterval)) {
            setStepError('Bitte Frequenz und Intervall angeben!');
            return false;
          }
        }
      }
    }

    if (currentStepKey === STEP_PERMISSIONS) {
      if (!event.permissionType) {
        setStepError('Bitte eine Sichtbarkeit wählen!');
        return false;
      }
    }

    return true;
  }, [currentStepKey, event, isMatchEvent, isTournament, isTask]);

  const handleNext = useCallback(() => {
    if (!validateCurrentStep()) return;
    setCurrentStep(prev => prev + 1);
  }, [validateCurrentStep]);

  const handleBack = useCallback(() => {
    setStepError(null);
    setCurrentStep(prev => Math.max(prev - 1, 0));
  }, []);

  const handleSave = useCallback(() => {
    if (!validateCurrentStep()) return;
    onSave(event);
  }, [event, onSave, validateCurrentStep]);

  const handleClose = useCallback(() => onClose(), [onClose]);

  // ── Tournament match helpers ───────────────────────────────────────────────
  const syncDraftsToParent = useCallback((matches?: any[]) => {
    if (event?.tournamentId) return;
    const drafts = (matches || tournamentMatches || [])
      .filter(m => String(m.id).startsWith('draft-'))
      .map(m => ({
        homeTeamId:   m.homeTeamId   || m.homeTeam || '',
        awayTeamId:   m.awayTeamId   || m.awayTeam || '',
        homeTeamName: m.homeTeamName || '',
        awayTeamName: m.awayTeamName || '',
        round:        m.round        || undefined,
        slot:         m.slot         || undefined,
        scheduledAt:  m.scheduledAt  || undefined,
      }));
    handleChange('pendingTournamentMatches', drafts);
  }, [event?.tournamentId, tournamentMatches, handleChange]);

  const handleTournamentMatchChange = useCallback((matchId: string) => {
    handleChange('tournamentMatchId', matchId);
    const match = tournamentMatches.find(x => String(x.id) === String(matchId));
    if (match) {
      if (match.homeTeamId) handleChange('homeTeam', String(match.homeTeamId));
      if (match.awayTeamId) handleChange('awayTeam', String(match.awayTeamId));
    }
  }, [handleChange, tournamentMatches]);

  const handleGeneratePlan = async () => {
    if (!event.tournamentId) return;
    try {
      const res = await apiRequest(`/api/tournaments/${event.tournamentId}/generate-plan`, { method: 'POST' });
      if (res.ok) {
        await reloadMatches(event.tournamentId, setTournamentMatches);
      } else if (res.status === 403) {
        alert('Keine Berechtigung, Turnierplan zu generieren.');
      } else {
        alert('Fehler beim Erzeugen des Turnierplans');
      }
    } catch { alert('Fehler beim Erzeugen des Turnierplans'); }
  };

  // Inline match editing (Begegnungen step)
  const handleAddMatch = useCallback(() => {
    const newDraft = {
      id: `draft-${Date.now()}`,
      round: '', slot: '', homeTeamId: '', awayTeamId: '',
      homeTeamName: '', awayTeamName: '', scheduledAt: '',
    };
    const updated = [...(tournamentMatches || []), newDraft];
    setTournamentMatches(updated);
    setEditingMatchId(newDraft.id);
    setEditingMatchDraft(newDraft);
    syncDraftsToParent(updated);
  }, [tournamentMatches, setTournamentMatches, syncDraftsToParent]);

  const handleEditMatch = useCallback((match: any) => {
    setEditingMatchId(match.id);
    setEditingMatchDraft({ ...match });
  }, []);

  const handleSaveMatch = useCallback(async () => {
    if (!editingMatchDraft) return;
    const updated = (tournamentMatches || []).map((x: any) =>
      x.id === editingMatchDraft.id ? { ...x, ...editingMatchDraft } : x
    );
    setTournamentMatches(updated);
    syncDraftsToParent(updated);
    setEditingMatchId(null);
    setEditingMatchDraft(null);
    if (event.tournamentId && !String(editingMatchDraft.id).startsWith('draft-')) {
      try {
        await apiRequest(`/api/tournaments/${event.tournamentId}/matches/${editingMatchDraft.id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(editingMatchDraft),
        });
        const res = await apiRequest(`/api/tournaments/${event.tournamentId}/matches`);
        setTournamentMatches(await res.json() || []);
      } catch { /* ignore */ }
    }
  }, [editingMatchDraft, tournamentMatches, event.tournamentId, setTournamentMatches, syncDraftsToParent]);

  const handleCancelEdit = useCallback(() => {
    setEditingMatchId(null);
    setEditingMatchDraft(null);
  }, []);

  const handleDeleteMatch = useCallback(async (matchId: string | number) => {
    if (String(matchId).startsWith('draft-')) {
      const updated = (tournamentMatches || []).filter((x: any) => x.id !== matchId);
      setTournamentMatches(updated);
      syncDraftsToParent(updated);
      return;
    }
    if (!event.tournamentId) return;
    try {
      await apiRequest(`/api/tournaments/${event.tournamentId}/matches/${matchId}`, { method: 'DELETE' });
      const res = await apiRequest(`/api/tournaments/${event.tournamentId}/matches`);
      setTournamentMatches(await res.json() || []);
    } catch { /* ignore */ }
  }, [tournamentMatches, event.tournamentId, setTournamentMatches, syncDraftsToParent]);

  // Import / Manual / Generator handlers
  const handleImportClose = async (payload?: any[]) => {
    setImportOpen(false);
    if (!event.tournamentId) {
      if (payload?.length) {
        handleChange('pendingTournamentMatches', payload);
        setTournamentMatches(
          payload.map((m: any, idx: number) => ({
            id: `draft-${idx}`, ...m,
            homeTeamName: teams.find(t => String(t.value) === String(m.homeTeamId))?.label || m.homeTeamName || '',
            awayTeamName: teams.find(t => String(t.value) === String(m.awayTeamId))?.label || m.awayTeamName || '',
          }))
        );
      }
      return;
    }
    await reloadMatches(event.tournamentId, setTournamentMatches);
  };

  const handleManualClose = async (payload?: any[]) => {
    setManualOpen(false);
    if (event.tournamentId) {
      await reloadMatches(event.tournamentId, setTournamentMatches);
      return;
    }
    if (payload?.length) {
      handleChange('pendingTournamentMatches', payload);
      setTournamentMatches(
        payload.map((m: any, idx: number) => ({
          id: `draft-${idx}`, ...m,
          homeTeamName: teams.find(t => String(t.value) === String(m.homeTeamId))?.label || m.homeTeamName || '',
          awayTeamName: teams.find(t => String(t.value) === String(m.awayTeamId))?.label || m.awayTeamName || '',
        }))
      );
    }
  };

  const handleGeneratorClose = (
    matches: any[],
    config?: { gameMode?: string; tournamentType?: string; roundDuration?: number; breakTime?: number; numberOfGroups?: number },
  ) => {
    handleChange('pendingTournamentMatches', matches);
    if (config) {
      if (config.gameMode)                     handleChange('tournamentGameMode',       config.gameMode);
      if (config.tournamentType)               handleChange('tournamentType',           config.tournamentType);
      if (config.roundDuration !== undefined)  handleChange('tournamentRoundDuration',  config.roundDuration);
      if (config.breakTime !== undefined)      handleChange('tournamentBreakTime',      config.breakTime);
      if (config.numberOfGroups !== undefined) handleChange('tournamentNumberOfGroups', config.numberOfGroups);
    }
    setTournamentMatches(
      matches.map((m: any, idx: number) => ({
        id: `draft-${idx}`, ...m,
        homeTeamName: teams.find(t => String(t.value) === String(m.homeTeamId))?.label || m.homeTeamName || '',
        awayTeamName: teams.find(t => String(t.value) === String(m.awayTeamId))?.label || m.awayTeamName || '',
      }))
    );
    setGeneratorOpen(false);
  };

  // ── Step content ──────────────────────────────────────────────────────────
  const renderStepContent = () => {
    switch (currentStepKey) {

      case STEP_BASE:
        return (
          <EventBaseForm
            formData={event}
            eventTypes={eventTypes}
            locations={locations}
            handleChange={handleChange}
          />
        );

      case STEP_DETAILS:
        return (
          <Box sx={{ display: 'flex', flexDirection: 'column' }}>
            {/* Location — shown for match and training events */}
            {(isMatchEvent || isTraining) && (
              <LocationField
                locations={locations}
                value={event.locationId}
                onChange={v => handleChange('locationId', v)}
              />
            )}

            {/* Match / Tournament fields */}
            {isMatchEvent && (
              <>
                <GameEventFields
                  formData={event}
                  teams={matchTeams}
                  gameTypes={gameTypes}
                  leagues={leagues}
                  isTournament={isTournament}
                  isTournamentEventType={isTournamentEventType}
                  handleChange={handleChange}
                />
                {(isTournament || event.tournamentId) && (
                  <Box sx={{ mt: 1 }}>
                    <TournamentSelection
                      formData={event}
                      tournaments={tournaments}
                      tournamentMatches={tournamentMatches}
                      onChange={handleChange}
                      onTournamentMatchChange={handleTournamentMatchChange}
                    />
                    <TournamentConfig
                      formData={event}
                      isExistingTournament={!!event.tournamentId}
                      onChange={handleChange}
                    />
                    <TournamentMatchesManagement
                      tournamentMatches={tournamentMatches}
                      onImportOpen={() => setImportOpen(true)}
                      onManualOpen={() => setManualOpen(true)}
                      onGeneratorOpen={() => setGeneratorOpen(true)}
                      onGeneratePlan={handleGeneratePlan}
                      onClearMatches={() => {
                        handleChange('pendingTournamentMatches', []);
                        setTournamentMatches([]);
                      }}
                      showOldGeneration={!!event.tournamentId}
                    />
                  </Box>
                )}
              </>
            )}

            {/* Task fields */}
            {isTask && (
              <TaskEventFields formData={event} users={users} handleChange={handleChange} />
            )}

            {/* Training fields */}
            {isTraining && (
              <TrainingEventFields formData={event} teams={teams} handleChange={handleChange} />
            )}

            {/* No type selected yet — hint */}
            {!isMatchEvent && !isTask && !isTraining && !isGenericEvent && (
              <Box sx={{ py: 6, textAlign: 'center' }}>
                <Typography color="text.secondary" variant="body2">
                  Bitte zuerst den Event-Typ auf Schritt 1 auswählen.
                </Typography>
              </Box>
            )}
          </Box>
        );

      case STEP_MATCHES:
        return (
          <WizardStep2Tournament
            tournamentMatches={tournamentMatches}
            teams={matchTeams}
            editingMatchId={editingMatchId}
            editingMatchDraft={editingMatchDraft}
            onAddMatch={handleAddMatch}
            onEditMatch={handleEditMatch}
            onSaveMatch={handleSaveMatch}
            onCancelEdit={handleCancelEdit}
            onDeleteMatch={handleDeleteMatch}
            setEditingMatchDraft={setEditingMatchDraft}
          />
        );

      case STEP_PERMISSIONS:
        return (
          <Box sx={{ display: 'flex', flexDirection: 'column' }}>
            <LocationField
              locations={locations}
              value={event.locationId}
              onChange={v => handleChange('locationId', v)}
            />
            <PermissionFields
              formData={event}
              teams={teams}
              users={users}
              handleChange={handleChange}
            />
          </Box>
        );

      case STEP_DESCRIPTION:
        return (
          <TextField
            label="Beschreibung"
            value={event.description || ''}
            onChange={e => handleChange('description', e.target.value)}
            fullWidth
            margin="normal"
            multiline
            rows={isMobile ? 6 : 8}
            placeholder="Optionale Beschreibung für dieses Event …"
          />
        );

      default:
        return null;
    }
  };

  // ── Modal title with step indicator ───────────────────────────────────────
  const modalTitle = event.title
    ? `${event.title}`
    : 'Neues Event';

  // ── Actions ───────────────────────────────────────────────────────────────
  const modalActions = (
    <Box
      sx={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        width: '100%',
        flexWrap: 'wrap',
        gap: 1,
      }}
    >
      {/* Left: Cancel + Delete */}
      <Box sx={{ display: 'flex', gap: 1 }}>
        <Button
          onClick={handleClose}
          color="secondary"
          variant="outlined"
          disabled={loading}
          size="small"
        >
          Abbrechen
        </Button>
        {showDelete && onDelete && (
          <Button
            onClick={onDelete}
            color="error"
            variant="outlined"
            disabled={loading}
            size="small"
            startIcon={<DeleteOutlineIcon />}
          >
            Löschen
          </Button>
        )}
      </Box>

      {/* Right: Back + Next / Save */}
      <Box sx={{ display: 'flex', gap: 1 }}>
        {currentStep > 0 && (
          <Button
            onClick={handleBack}
            variant="outlined"
            startIcon={<ArrowBackIcon />}
            disabled={loading}
            size="small"
          >
            Zurück
          </Button>
        )}
        {!isLastStep ? (
          <Button
            onClick={handleNext}
            variant="contained"
            endIcon={<ArrowForwardIcon />}
            size="small"
          >
            Weiter
          </Button>
        ) : (
          <Button
            onClick={handleSave}
            variant="contained"
            color="primary"
            startIcon={<SaveIcon />}
            disabled={loading}
            size="small"
          >
            {loading ? 'Wird gespeichert …' : 'Speichern'}
          </Button>
        )}
      </Box>
    </Box>
  );

  // ── Render ────────────────────────────────────────────────────────────────
  return (
    <>
      <BaseModal
        open={open}
        onClose={onClose}
        title={modalTitle}
        maxWidth="md"
        fullScreen={isMobile}
        actions={modalActions}
      >
        {/* Stepper */}
        <Box sx={{ mb: { xs: 2, sm: 3 } }}>
          <Stepper
            activeStep={currentStep}
            alternativeLabel
            sx={{
              '& .MuiStepLabel-label': { fontSize: { xs: '0.7rem', sm: '0.875rem' } },
              '& .MuiStepIcon-root':   { fontSize: { xs: '1.2rem',  sm: '1.5rem'  } },
            }}
          >
            {steps.map((step, index) => (
              <Step key={step.key} completed={index < currentStep}>
                <StepLabel>{step.label}</StepLabel>
              </Step>
            ))}
          </Stepper>
        </Box>

        {/* Per-step validation error */}
        {stepError && (
          <Alert severity="error" sx={{ mb: 2 }} onClose={() => setStepError(null)}>
            {stepError}
          </Alert>
        )}

        {/* Step content */}
        <Box sx={{ minHeight: { xs: 180, sm: 240 } }}>
          {renderStepContent()}
        </Box>
      </BaseModal>

      {/* ── Sub-dialogs ──────────────────────────────────────────────────── */}
      {importOpen && (
        <ImportMatchesDialog
          open={importOpen}
          onClose={() => setImportOpen(false)}
          tournamentId={event.tournamentId}
          initialMatches={tournamentMatches}
          onImported={handleImportClose}
        />
      )}

      {manualOpen && (
        <ManualMatchesEditor
          open={manualOpen}
          onClose={() => setManualOpen(false)}
          tournamentId={event.tournamentId}
          teams={matchTeams}
          matchTeams={event.teamIds}
          initialMatches={tournamentMatches}
          gameMode={event.tournamentGameMode || 'round_robin'}
          roundDuration={event.tournamentRoundDuration || 10}
          breakTime={event.tournamentBreakTime || 2}
          onSaved={handleManualClose}
        />
      )}

      {generatorOpen && (
        <TournamentMatchGeneratorDialog
          open={generatorOpen}
          onClose={() => setGeneratorOpen(false)}
          teams={matchTeams}
          matchTeams={event.teamIds}
          tournament={event.tournament}
          initialMatches={tournamentMatches}
          startDate={event.date}
          startTime={event.time}
          initialGameMode={event.tournamentGameMode || 'round_robin'}
          initialTournamentType={event.tournamentType || 'indoor_hall'}
          initialRoundDuration={event.tournamentRoundDuration || 10}
          initialBreakTime={event.tournamentBreakTime || 2}
          initialNumberOfGroups={event.tournamentNumberOfGroups || 2}
          onGenerate={handleGeneratorClose}
        />
      )}
    </>
  );
};
