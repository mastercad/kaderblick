import React, { useState } from 'react';
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
import { EventData, SelectOption, User } from '../../types/event';
import { calculateWizardSteps } from '../../utils/eventHelpers';
import {
  WizardStep0,
  WizardStep1,
  WizardStep2Tournament,
  WizardStep2Permissions,
  WizardStepDescription,
  WizardStepSummary,
} from './WizardSteps';

import ImportMatchesDialog from '../../modals/ImportMatchesDialog';
import ManualMatchesEditor from '../../modals/ManualMatchesEditor';
import TournamentMatchGeneratorDialog from '../../modals/TournamentMatchGeneratorDialog';
import { apiRequest } from '../../utils/api';

interface EventWizardProps {
  open: boolean;
  onClose: () => void;
  formData: EventData;
  eventTypes: SelectOption[];
  locations: SelectOption[];
  teams: SelectOption[];
  gameTypes: SelectOption[];
  leagues: SelectOption[];
  tournaments: SelectOption[];
  users: User[];
  tournamentMatches: any[];
  setTournamentMatches: (matches: any[]) => void;
  isGameEvent: boolean;
  isTaskEvent: boolean;
  isTournament: boolean;
  onChange: (field: string, value: any) => void;
  syncDraftsToParent: (matches?: any[]) => void;
}

/**
 * Event creation/editing wizard with step-by-step guidance
 */
export const EventWizard: React.FC<EventWizardProps> = ({
  open,
  onClose,
  formData,
  eventTypes,
  locations,
  teams,
  gameTypes,
  leagues,
  tournaments,
  users,
  tournamentMatches,
  setTournamentMatches,
  isGameEvent,
  isTaskEvent,
  isTournament,
  onChange,
  syncDraftsToParent,
}) => {
  const [wizardStep, setWizardStep] = useState(0);
  const [wizardError, setWizardError] = useState<string | null>(null);
  const [editingMatchId, setEditingMatchId] = useState<string | number | null>(null);
  const [editingMatchDraft, setEditingMatchDraft] = useState<any>(null);
  const [importOpen, setImportOpen] = useState(false);
  const [manualOpen, setManualOpen] = useState(false);
  const [generatorOpen, setGeneratorOpen] = useState(false);

  // Keine lokalen States mehr, alles läuft über zentrale Props
  React.useEffect(() => {
    if (open) {
      setWizardStep(0);
      setWizardError(null);
      setEditingMatchId(null);
      setEditingMatchDraft(null);
    }
  }, [open]);

  const { descriptionStep, summaryStep } = calculateWizardSteps(isGameEvent, isTaskEvent, isTournament);

  const handleClose = () => {
    onClose();
  };

  const validateStep = (step: number): boolean => {
    setWizardError(null);

    if (step === 0) {
      if (!formData.title || !formData.eventType || !formData.date) {
        setWizardError('Bitte Titel, Event-Typ und Start-Datum angeben!');
        return false;
      }
    }

    if (step === 1) {
      if (isGameEvent) {
        if (!isTournament && (!formData.homeTeam || !formData.awayTeam)) {
          setWizardError('Bitte Heim- und Auswärts-Team angeben!');
          return false;
        }
        if (!formData.locationId) {
          setWizardError('Bitte Austragungsort auswählen!');
          return false;
        }
      }
      if (isTaskEvent) {
        if (!formData.taskRotationUsers || formData.taskRotationUsers.length === 0) {
          setWizardError('Bitte mindestens einen Benutzer für die Rotation auswählen!');
          return false;
        }
        if (!formData.taskRotationCount || formData.taskRotationCount < 1) {
          setWizardError('Bitte eine gültige Anzahl Personen pro Aufgabe angeben!');
          return false;
        }
        if (formData.taskIsRecurring) {
          if (!formData.taskRecurrenceMode) {
            setWizardError('Bitte Wiederkehr-Modus wählen!');
            return false;
          }
          if (formData.taskRecurrenceMode === 'classic') {
            if (!formData.taskFreq || !formData.taskInterval) {
              setWizardError('Bitte Frequenz und Intervall angeben!');
              return false;
            }
          }
        }
      }
    }

    if (step === 2 && !isGameEvent && !isTaskEvent) {
      if (!formData.permissionType) {
        setWizardError('Bitte eine Sichtbarkeit wählen!');
        return false;
      }
    }

    return true;
  };

  const handleNext = () => {
    if (!validateStep(wizardStep)) {
      return;
    }
    setWizardStep(prev => prev + 1);
  };

  const handleBack = () => {
    setWizardError(null);
    setWizardStep(prev => Math.max(prev - 1, 0));
  };

  // Tournament match management handlers
  const handleAddMatch = () => {
    const newDraft = {
      id: `draft-${Date.now()}`,
      round: '',
      slot: '',
      homeTeamId: '',
      awayTeamId: '',
      homeTeamName: '',
      awayTeamName: '',
      scheduledAt: '',
    };
    const updated = [...(tournamentMatches || []), newDraft];
    setTournamentMatches(updated);
    setEditingMatchId(newDraft.id);
    setEditingMatchDraft(newDraft);
    syncDraftsToParent(updated);
  };

  const handleEditMatch = (match: any) => {
    setEditingMatchId(match.id);
    setEditingMatchDraft({ ...match });
  };

  const handleSaveMatch = async () => {
    if (!editingMatchDraft) return;

    // Optimistic update
    const updated = (tournamentMatches || []).map((x: any) =>
      x.id === editingMatchDraft.id ? { ...x, ...editingMatchDraft } : x
    );
    setTournamentMatches(updated);
    syncDraftsToParent(updated);
    setEditingMatchId(null);
    setEditingMatchDraft(null);

    // If this is a persisted tournament match, try to persist changes
    if (formData.tournamentId && String(editingMatchDraft.id).indexOf('draft-') === -1) {
      try {
        await apiRequest(`/api/tournaments/${formData.tournamentId}/matches/${editingMatchDraft.id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(editingMatchDraft),
        });
        const res2 = await apiRequest(`/api/tournaments/${formData.tournamentId}/matches`);
        const data = await res2.json();
        setTournamentMatches(data || []);
      } catch (e) {
        // ignore
      }
    }
  };

  const handleCancelEdit = () => {
    setEditingMatchId(null);
    setEditingMatchDraft(null);
  };

  const handleDeleteMatch = async (matchId: string | number) => {
    if (String(matchId).indexOf('draft-') === 0) {
      const updated = (tournamentMatches || []).filter((x: any) => x.id !== matchId);
      setTournamentMatches(updated);
      syncDraftsToParent(updated);
      return;
    }

    if (!formData.tournamentId) return;

    try {
      await apiRequest(`/api/tournaments/${formData.tournamentId}/matches/${matchId}`, {
        method: 'DELETE',
      });
      const res2 = await apiRequest(`/api/tournaments/${formData.tournamentId}/matches`);
      const data = await res2.json();
      setTournamentMatches(data || []);
    } catch (e) {
      // ignore
    }
  };

  const getStepContent = () => {
    if (wizardStep === 0) {
      return (
        <WizardStep0
          formData={formData}
          eventTypes={eventTypes}
          locations={locations}
          onChange={onChange}
        />
      );
    }

    if (wizardStep === 1) {
      return (
        <WizardStep1
          formData={formData}
          locations={locations}
          teams={teams}
          gameTypes={gameTypes}
          leagues={leagues}
          tournaments={tournaments}
          users={users}
          isGameEvent={isGameEvent}
          isTaskEvent={isTaskEvent}
          isTournament={isTournament}
          onChange={onChange}
          tournamentMatches={tournamentMatches}
          onImportOpen={() => setImportOpen(true)}
          onManualOpen={() => setManualOpen(true)}
          onGeneratorOpen={() => setGeneratorOpen(true)}
          onClearMatches={() => setTournamentMatches([])}
        />
      );
    }

    if (wizardStep === 2) {
      if (isTournament) {
        return (
          <WizardStep2Tournament
            tournamentMatches={tournamentMatches}
            teams={teams}
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
      }

      if (!isGameEvent && !isTaskEvent) {
        return (
          <WizardStep2Permissions
            formData={formData}
            teams={teams}
            users={users}
            onChange={onChange}
          />
        );
      }
    }

    if (wizardStep === descriptionStep) {
      return <WizardStepDescription formData={formData} onChange={onChange} />;
    }

    if (wizardStep === summaryStep) {
      return (
        <WizardStepSummary
          formData={formData}
          eventTypes={eventTypes}
          locations={locations}
          teams={teams}
          gameTypes={gameTypes}
          leagues={leagues}
          tournaments={tournaments}
          users={users}
          tournamentMatches={tournamentMatches}
          isGameEvent={isGameEvent}
          isTaskEvent={isTaskEvent}
          isTournament={isTournament}
        />
      );
    }

    return null;
  };

  const steps = [
    'Basisdaten',
    'Details',
    ...(isTournament ? ['Begegnungen'] : []),
    ...(!isGameEvent && !isTaskEvent ? ['Berechtigungen'] : []),
    'Beschreibung',
    'Zusammenfassung',
  ];

  return (
    <Dialog open={open} onClose={handleClose} maxWidth="md" fullWidth>
      <DialogTitle>
        Event Wizard
        <IconButton
          aria-label="close"
          onClick={handleClose}
          sx={{ position: 'absolute', right: 8, top: 8 }}
        >
          <CloseIcon />
        </IconButton>
      </DialogTitle>

      <DialogContent>
        <Stepper activeStep={wizardStep} alternativeLabel>
          {steps.map(label => (
            <Step key={label}>
              <StepLabel>{label}</StepLabel>
            </Step>
          ))}
        </Stepper>

        <div style={{ marginTop: 32 }}>
          {wizardError && <div style={{ color: 'red', marginBottom: 12 }}>{wizardError}</div>}
          {/* Only render the current step's content */}
          {getStepContent()}
        </div>
      </DialogContent>

      <DialogActions>
        <Button onClick={handleBack} disabled={wizardStep === 0}>
          Zurück
        </Button>

        {wizardStep < summaryStep ? (
          <Button onClick={handleNext} color="primary">
            Weiter
          </Button>
        ) : (
          <Button onClick={handleClose} color="success">
            Fertig
          </Button>
        )}
      </DialogActions>

      {/* Dialogs for tournament match management */}
      <ImportMatchesDialog
        open={importOpen}
        onClose={() => setImportOpen(false)}
        tournamentId={formData.tournamentId}
        onImported={() => setImportOpen(false)}
      />
      <ManualMatchesEditor
        open={manualOpen}
        onClose={() => setManualOpen(false)}
        tournamentId={formData.tournamentId}
        teams={teams}
        initialMatches={tournamentMatches}
        gameMode={formData.tournamentGameMode || 'round_robin'}
        roundDuration={formData.tournamentRoundDuration || 10}
        breakTime={formData.tournamentBreakTime || 2}
        onSaved={() => setManualOpen(false)}
      />
      <TournamentMatchGeneratorDialog
        open={generatorOpen}
        onClose={() => setGeneratorOpen(false)}
        teams={teams}
        startDate={formData.date}
        startTime={formData.time}
        initialGameMode={formData.tournamentGameMode || 'round_robin'}
        initialTournamentType={formData.tournamentType || 'indoor_hall'}
        initialRoundDuration={formData.tournamentRoundDuration || 10}
        initialBreakTime={formData.tournamentBreakTime || 2}
        initialNumberOfGroups={formData.tournamentNumberOfGroups || 2}
        onGenerate={() => setGeneratorOpen(false)}
      />
    </Dialog>
  );
};
