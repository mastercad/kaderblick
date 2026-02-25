import React, { useState, useEffect, useCallback } from 'react';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Autocomplete from '@mui/material/Autocomplete';
import BaseModal from './BaseModal';
import ImportMatchesDialog from './ImportMatchesDialog';
import ManualMatchesEditor from './ManualMatchesEditor';
import TournamentMatchGeneratorDialog from './TournamentMatchGeneratorDialog';
import { EventBaseForm } from '../components/EventModal/EventBaseForm';
import { GameEventFields } from '../components/EventModal/GameEventFields';
import { TaskEventFields } from '../components/EventModal/TaskEventFields';
import { PermissionFields } from '../components/EventModal/PermissionFields';
import {
  TournamentConfig,
  TournamentMatchesManagement,
  TournamentSelection,
} from '../components/EventModal/TournamentFields';
import { EventWizard } from '../components/EventModal/EventWizard';
import { useTournamentMatches, useLeagues, useReloadTournamentMatches } from '../hooks/useEventData';
import { useEventTypeFlags } from '../hooks/useEventTypeFlags';
import { EventData, SelectOption, User } from '../types/event';
import { apiRequest } from '../utils/api';

interface EventModalProps {
  open: boolean;
  onClose: () => void;
  onSave: (formData: EventData) => void;
  onDelete?: () => void;
  showDelete?: boolean;
  event: EventData;
  eventTypes: SelectOption[];
  teams?: SelectOption[];
  gameTypes?: SelectOption[];
  locations: SelectOption[];
  tournaments?: SelectOption[];
  users?: User[];
  loading?: boolean;
  onChange: (field: string, value: any) => void;
}

export const EventModal: React.FC<EventModalProps> = ({
  open,
  onClose,
  onSave,
  onDelete,
  showDelete = false,
  event,
  eventTypes,
  teams = [],
  gameTypes = [],
  locations,
  tournaments = [],
  users = [],
  loading = false,
  onChange,
}) => {
  const { tournamentMatches, setTournamentMatches } = useTournamentMatches(event.tournamentId, open);
  const [wizardOpen, setWizardOpen] = useState(false);
  const [importOpen, setImportOpen] = useState(false);
  const [manualOpen, setManualOpen] = useState(false);
  const [generatorOpen, setGeneratorOpen] = useState(false);

  // Turnier-Settings aus der API in Event-Felder übernehmen
  useEffect(() => {
    const fetchTournamentSettings = async () => {
      if (open && event.tournamentId) {
        try {
          const res = await apiRequest(`/api/tournaments/${event.tournamentId}`);
          if (res.ok) {
            const data = await res.json();
            // Settings in Event-Felder mappen
            if (data.settings) {
              if (data.settings.type) onChange('tournamentType', data.settings.type);
              if (data.settings.roundDuration) onChange('tournamentRoundDuration', data.settings.roundDuration);
              if (data.settings.breakTime) onChange('tournamentBreakTime', data.settings.breakTime);
              if (data.settings.gameMode) onChange('tournamentGameMode', data.settings.gameMode);
              if (data.settings.numberOfGroups) onChange('tournamentNumberOfGroups', data.settings.numberOfGroups);
            }
          }
        } catch (e) {
          // ignore
        }
      }
    };
    fetchTournamentSettings();
  }, [open, event.tournamentId]);

  // Custom hooks for data management
  const leagues = useLeagues(open);
  const reloadMatches = useReloadTournamentMatches();

  // Populate tournamentMatches from all available sources:
  // 1. pendingTournamentMatches (drafted in generator, not yet saved)
  // 2. tournament.matches (embedded from backend when editing existing event)
  // 3. useTournamentMatches hook (API call for existing tournamentId)
  useEffect(() => {
    if (!open) return;

    // Priority 1: Pending draft matches
    if (event?.pendingTournamentMatches && event.pendingTournamentMatches.length > 0) {
      setTournamentMatches(
        event.pendingTournamentMatches.map((match: any, idx: number) => ({
          id: match.id || `draft-${idx}`,
          ...match,
          homeTeamName:
            teams.find(team => String(team.value) === String(match.homeTeamId))?.label ||
            match.homeTeamName ||
            '',
          awayTeamName:
            teams.find(team => String(team.value) === String(match.awayTeamId))?.label ||
            match.awayTeamName ||
            '',
        }))
      );
      return;
    }

    // Priority 2: Embedded matches from tournament object (loaded via handleEditEvent)
    if (event?.tournament?.matches && event.tournament.matches.length > 0) {
      setTournamentMatches(
        event.tournament.matches.map((match: any) => ({
          ...match,
          id: match.id || `embedded-${match.round}-${match.slot}`,
          homeTeamName:
            teams.find(team => String(team.value) === String(match.homeTeamId))?.label ||
            match.homeTeamName ||
            '',
          awayTeamName:
            teams.find(team => String(team.value) === String(match.awayTeamId))?.label ||
            match.awayTeamName ||
            '',
        }))
      );
    }
    // Priority 3: useTournamentMatches hook already loads from API when tournamentId exists
  }, [open, event?.pendingTournamentMatches?.length, event?.tournament?.matches?.length, teams]);

  // Single source of truth for event type classification
  const { isMatchEvent, isTournament, isTournamentEventType, isTask, isGenericEvent } = useEventTypeFlags(
    event.eventType, event.gameType, eventTypes, gameTypes,
  );

  // Pass changes up to parent
  const handleChange = useCallback((field: string, value: any) => {
    if (typeof onChange === 'function') {
      onChange(field, value);
    }
  }, [onChange]);

  // Auto-fill gameType when CalendarEventType is "Turnier" but no game exists.
  // Without a Game entity, gameType is empty — this ensures both wizard paths
  // ("Turnier" and "Spiel + GameType Turnier") render identically.
  useEffect(() => {
    if (open && isTournament && !event.gameType && gameTypes.length > 0) {
      const turnierGameType = gameTypes.find(
        gt => gt.label.toLowerCase().includes('turnier')
      );
      if (turnierGameType) {
        handleChange('gameType', turnierGameType.value);
      }
    }
  }, [open, isTournament, event.gameType, gameTypes, handleChange]);

  // Save: pass current event data to parent
  const handleSave = useCallback(() => {
    onSave(event);
  }, [event, onSave]);

  // Handle close without saving
  const handleClose = useCallback(() => {
    onClose();
  }, [onClose]);

  // Sync draft matches to parent - memoized
  const syncDraftsToParent = useCallback((matches?: any[]) => {
    if (event?.tournamentId) return;
    const drafts = (matches || tournamentMatches || [])
      .filter(match => String(match.id).startsWith('draft-'))
      .map(match => ({
        homeTeamId: match.homeTeamId || match.homeTeam || '',
        awayTeamId: match.awayTeamId || match.awayTeam || '',
        homeTeamName: match.homeTeamName || '',
        awayTeamName: match.awayTeamName || '',
        round: match.round || undefined,
        slot: match.slot || undefined,
        scheduledAt: match.scheduledAt || undefined,
      }));
    handleChange('pendingTournamentMatches', drafts);
  }, [event?.tournamentId, tournamentMatches, handleChange]);

  // Handle tournament match selection - memoized
  const handleTournamentMatchChange = useCallback((matchId: string) => {
    handleChange('tournamentMatchId', matchId);
    const match = tournamentMatches.find(x => String(x.id) === String(matchId));
    if (match) {
      if (match.homeTeamId) handleChange('homeTeam', String(match.homeTeamId));
      if (match.awayTeamId) handleChange('awayTeam', String(match.awayTeamId));
    }
  }, [handleChange, tournamentMatches]);

  // Generate tournament plan from backend
  const handleGeneratePlan = async () => {
    if (!event.tournamentId) return;
    try {
      const res = await apiRequest(`/api/tournaments/${event.tournamentId}/generate-plan`, {
        method: 'POST',
      });
      if (res.ok) {
        await reloadMatches(event.tournamentId, setTournamentMatches);
      } else if (res.status === 403) {
        alert('Keine Berechtigung, Turnierplan zu generieren.');
      } else {
        alert('Fehler beim Erzeugen des Turnierplans');
      }
    } catch (e) {
      alert('Fehler beim Erzeugen des Turnierplans');
    }
  };

  // Handle import matches dialog close
  const handleImportClose = async (payload?: any[]) => {
    setImportOpen(false);
    if (!event.tournamentId) {
      // Draft mode: receive payload and set pending matches
      if (payload && payload.length > 0) {
        handleChange('pendingTournamentMatches', payload);
        setTournamentMatches(
          payload.map((m: any, idx: number) => ({
            id: `draft-${idx}`,
            ...m,
            homeTeamName:
              teams.find(t => String(t.value) === String(m.homeTeamId))?.label || m.homeTeamName || '',
            awayTeamName:
              teams.find(t => String(t.value) === String(m.awayTeamId))?.label || m.awayTeamName || '',
          }))
        );
      }
      return;
    }
    await reloadMatches(event.tournamentId, setTournamentMatches);
  };

  // Handle manual matches editor close
  const handleManualClose = async (payload?: any[]) => {
    setManualOpen(false);
    if (event.tournamentId) {
      await reloadMatches(event.tournamentId, setTournamentMatches);
      return;
    }
    // Draft mode: store draft matches
    if (payload && payload.length > 0) {
      handleChange('pendingTournamentMatches', payload);
      setTournamentMatches(
        payload.map((m: any, idx: number) => ({
          id: `draft-${idx}`,
          ...m,
          homeTeamName:
            teams.find(t => String(t.value) === String(m.homeTeamId))?.label || m.homeTeamName || '',
          awayTeamName:
            teams.find(t => String(t.value) === String(m.awayTeamId))?.label || m.awayTeamName || '',
        }))
      );
    }
  };

  // Handle tournament match generator close
  const handleGeneratorClose = (matches: any[], config?: { gameMode?: string; tournamentType?: string; roundDuration?: number; breakTime?: number; numberOfGroups?: number }) => {
    handleChange('pendingTournamentMatches', matches);
    // Sync tournament config back to event state
    if (config) {
      if (config.gameMode) handleChange('tournamentGameMode', config.gameMode);
      if (config.tournamentType) handleChange('tournamentType', config.tournamentType);
      if (config.roundDuration !== undefined) handleChange('tournamentRoundDuration', config.roundDuration);
      if (config.breakTime !== undefined) handleChange('tournamentBreakTime', config.breakTime);
      if (config.numberOfGroups !== undefined) handleChange('tournamentNumberOfGroups', config.numberOfGroups);
    }
    setTournamentMatches(
      matches.map((m: any, idx: number) => ({
        id: `draft-${idx}`,
        ...m,
        homeTeamName:
          teams.find(t => String(t.value) === String(m.homeTeamId))?.label || m.homeTeamName || '',
        awayTeamName:
          teams.find(t => String(t.value) === String(m.awayTeamId))?.label || m.awayTeamName || '',
      }))
    );
    setGeneratorOpen(false);
  };

  return (
    <>
      <BaseModal
        open={open}
        onClose={onClose}
        title="Event verwalten"
        maxWidth="lg"
        actions={
          <>
            <Button onClick={handleClose} color="secondary" variant="outlined" disabled={loading}>
              Abbrechen
            </Button>
            {showDelete && onDelete && (
              <Button onClick={onDelete} color="error" variant="outlined" disabled={loading}>
                Löschen
              </Button>
            )}
            <Button onClick={handleSave} color="primary" variant="contained" disabled={loading}>
              {loading ? 'Wird gespeichert...' : 'Speichern'}
            </Button>
            <Button
              onClick={() => setWizardOpen(true)}
              color="info"
              variant="outlined"
              style={{ marginLeft: 12 }}
            >
              Wizard starten
            </Button>
          </>
        }
      >
        <div style={{ display: 'flex', gap: 24 }}>
          <div style={{ flex: 1 }}>
            <EventBaseForm
              formData={event}
              eventTypes={eventTypes}
              locations={locations}
              handleChange={handleChange}
            />
          </div>

          <div style={{ flex: 1 }}>
            <Autocomplete
              options={locations}
              getOptionLabel={(option) => option.label}
              value={locations.find(l => l.value === event.locationId) || null}
              onChange={(_, newValue) => handleChange('locationId', newValue?.value || '')}
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

            {/* Match events (Spiel + Turnier): unified rendering */}
            {isMatchEvent && (
              <>
                <GameEventFields
                  formData={event}
                  teams={teams}
                  gameTypes={gameTypes}
                  leagues={leagues}
                  isTournament={isTournament}
                  isTournamentEventType={isTournamentEventType}
                  handleChange={handleChange}
                />

                {(isTournament || event.tournamentId) && (
                  <>
                    <TournamentSelection
                      formData={event}
                      tournaments={tournaments}
                      tournamentMatches={tournamentMatches}
                      onChange={handleChange}
                      onTournamentMatchChange={handleTournamentMatchChange}
                    />

                    <div style={{ marginTop: 8 }}>
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
                    </div>
                  </>
                )}
              </>
            )}

            {isTask && (
              <TaskEventFields formData={event} users={users} handleChange={handleChange} />
            )}

            {isGenericEvent && (
              <PermissionFields
                formData={event}
                teams={teams}
                users={users}
                handleChange={handleChange}
              />
            )}

            <TextField
              label="Beschreibung"
              value={event.description || ''}
              onChange={e => handleChange('description', e.target.value)}
              fullWidth
              margin="normal"
              multiline
              rows={4}
            />
          </div>
        </div>
      </BaseModal>

      {wizardOpen && (
        <EventWizard
          open={wizardOpen}
          onClose={() => setWizardOpen(false)}
          formData={event}
          eventTypes={eventTypes}
          locations={locations}
          teams={teams}
          gameTypes={gameTypes}
          leagues={leagues}
          tournaments={tournaments}
          users={users}
          tournamentMatches={tournamentMatches}
          setTournamentMatches={setTournamentMatches}
          onChange={handleChange}
          syncDraftsToParent={syncDraftsToParent}
        />
      )}

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
          teams={teams}
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
          teams={teams}
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
