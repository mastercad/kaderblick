import React from 'react';
import TextField from '@mui/material/TextField';
import Autocomplete from '@mui/material/Autocomplete';
import Button from '@mui/material/Button';
import { EventData, SelectOption, User } from '../../types/event';
import { EventBaseForm } from './EventBaseForm';
import { GameEventFields } from './GameEventFields';
import { TaskEventFields } from './TaskEventFields';
import { PermissionFields } from './PermissionFields';
import { getUserLabel } from '../../utils/eventHelpers';
import { TournamentSelection, TournamentConfig, TournamentMatchesManagement } from './TournamentFields';

interface WizardStep0Props {
  formData: EventData;
  eventTypes: SelectOption[];
  locations: SelectOption[];
  onChange: (field: string, value: any) => void;
}

/**
 * Wizard Step 0: Basic data (title, type, dates)
 */
export const WizardStep0: React.FC<WizardStep0Props> = (props) => {
  // Use handleChange for consistency with modal
  return <EventBaseForm {...props} handleChange={props.onChange} />;
};

interface WizardStep1Props {
  formData: EventData;
  locations: SelectOption[];
  teams: SelectOption[];
  gameTypes: SelectOption[];
  leagues: SelectOption[];
  tournaments: SelectOption[];
  users: User[];
  isGameEvent: boolean;
  isTaskEvent: boolean;
  isTournament: boolean;
  onChange: (field: string, value: any) => void;
  tournamentMatches?: any[];
  onImportOpen?: () => void;
  onManualOpen?: () => void;
  onGeneratorOpen?: () => void;
  onClearMatches?: () => void;
}

/**
 * Wizard Step 1: Details based on event type
 */
export const WizardStep1: React.FC<WizardStep1Props> = ({
  formData,
  locations,
  teams,
  gameTypes,
  leagues,
  tournaments,
  users,
  isGameEvent,
  isTaskEvent,
  isTournament,
  onChange,
  tournamentMatches,
  onImportOpen,
  onManualOpen,
  onGeneratorOpen,
  onClearMatches,
}) => {
  // Provide default no-op functions for optional handlers
  const handleImportOpen = onImportOpen || (() => {});
  const handleManualOpen = onManualOpen || (() => {});
  const handleGeneratorOpen = onGeneratorOpen || (() => {});
  const handleClearMatches = onClearMatches || (() => {});
  // Only render the detail fields for step 1, not the base form (step 0)
  return (
    <>
      {isGameEvent && (
        <>
          {/* Tournament workflow like in modal */}
          <Autocomplete
            options={locations}
            getOptionLabel={(option) => option.label}
            value={locations.find(l => l.value === formData.locationId) || null}
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
          <GameEventFields
            formData={formData}
            teams={teams}
            gameTypes={gameTypes}
            leagues={leagues}
            isTournament={isTournament}
            handleChange={onChange}
          />
          {(isTournament || formData.tournamentId) && (
            <>
              <TournamentSelection
                formData={formData}
                tournaments={tournaments}
                tournamentMatches={tournamentMatches || []}
                onChange={onChange}
                onTournamentMatchChange={() => {}}
              />
              <TournamentConfig
                formData={formData}
                isExistingTournament={!!formData.tournamentId}
                onChange={onChange}
              />
              <TournamentMatchesManagement
                tournamentMatches={tournamentMatches || []}
                onImportOpen={handleImportOpen}
                onManualOpen={handleManualOpen}
                onGeneratorOpen={handleGeneratorOpen}
                onClearMatches={handleClearMatches}
                showOldGeneration={!!formData.tournamentId}
              />
            </>
          )}
        </>
      )}
      {isTaskEvent && (
        <TaskEventFields
          formData={formData}
          users={users}
          handleChange={onChange}
        />
      )}
      {!isGameEvent && !isTaskEvent && (
        <PermissionFields
          formData={formData}
          teams={teams}
          users={users}
          handleChange={onChange}
        />
      )}
    </>
  );
};

interface WizardStep2TournamentProps {
  tournamentMatches: any[];
  teams: SelectOption[];
  editingMatchId: string | number | null;
  editingMatchDraft: any;
  onAddMatch: () => void;
  onEditMatch: (match: any) => void;
  onSaveMatch: () => void;
  onCancelEdit: () => void;
  onDeleteMatch: (matchId: string | number) => void;
  setEditingMatchDraft: (draft: any) => void;
}

/**
 * Wizard Step 2 for Tournament Games: Match management
 */
export const WizardStep2Tournament: React.FC<WizardStep2TournamentProps> = ({
  tournamentMatches,
  teams,
  editingMatchId,
  editingMatchDraft,
  onAddMatch,
  onEditMatch,
  onSaveMatch,
  onCancelEdit,
  onDeleteMatch,
  setEditingMatchDraft,
}) => {
  return (
    <>
      <h4>Begegnungen</h4>
      <div style={{ marginBottom: 12 }}>
        <div style={{ marginBottom: 8 }}>
          <Button size="small" variant="outlined" onClick={onAddMatch}>
            Neue Begegnung
          </Button>
        </div>
        <div>
          {(tournamentMatches || []).map((m: any) => (
            <div
              key={m.id}
              style={{
                padding: 8,
                border: '1px solid #eee',
                borderRadius: 6,
                marginBottom: 8,
                display: editingMatchId === m.id ? 'block' : 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
              }}
            >
              {editingMatchId === m.id ? (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                    <Autocomplete
                      options={teams}
                      getOptionLabel={(opt: any) => opt.label}
                      value={teams.find(t => String(t.value) === String(editingMatchDraft?.homeTeamId)) || null}
                      onChange={(_, nv) =>
                        setEditingMatchDraft((d: any) => ({
                          ...d,
                          homeTeamId: nv?.value || '',
                          homeTeamName: nv?.label || '',
                        }))
                      }
                      renderInput={(params) => <TextField {...params} label="Heim-Team" size="small" />}
                      sx={{ minWidth: 220 }}
                    />
                    <Autocomplete
                      options={teams}
                      getOptionLabel={(opt: any) => opt.label}
                      value={teams.find(t => String(t.value) === String(editingMatchDraft?.awayTeamId)) || null}
                      onChange={(_, nv) =>
                        setEditingMatchDraft((d: any) => ({
                          ...d,
                          awayTeamId: nv?.value || '',
                          awayTeamName: nv?.label || '',
                        }))
                      }
                      renderInput={(params) => <TextField {...params} label="Auswärts-Team" size="small" />}
                      sx={{ minWidth: 220 }}
                    />
                  </div>
                  <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                    <TextField
                      label="Runde"
                      size="small"
                      value={editingMatchDraft?.round || ''}
                      onChange={e => setEditingMatchDraft((d: any) => ({ ...d, round: e.target.value }))}
                      sx={{ minWidth: 120 }}
                    />
                    <TextField
                      label="Slot"
                      size="small"
                      value={editingMatchDraft?.slot || ''}
                      onChange={e => setEditingMatchDraft((d: any) => ({ ...d, slot: e.target.value }))}
                      sx={{ minWidth: 120 }}
                    />
                    <TextField
                      label="Datum/Zeit ISO"
                      size="small"
                      value={editingMatchDraft?.scheduledAt || ''}
                      onChange={e => setEditingMatchDraft((d: any) => ({ ...d, scheduledAt: e.target.value }))}
                      sx={{ minWidth: 200 }}
                    />
                  </div>
                  <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
                    <Button size="small" variant="contained" color="primary" onClick={onSaveMatch}>
                      Speichern
                    </Button>
                    <Button size="small" variant="outlined" onClick={onCancelEdit}>
                      Abbrechen
                    </Button>
                  </div>
                </div>
              ) : (
                <>
                  <div style={{ flex: 1 }}>
                    {`Runde ${m.round || '?'} - ${m.homeTeamName || 'TBD'} vs ${m.awayTeamName || 'TBD'}`}
                  </div>
                  <div style={{ marginLeft: 12, display: 'flex', gap: 8 }}>
                    <Button size="small" variant="outlined" onClick={() => onEditMatch(m)}>
                      Bearbeiten
                    </Button>
                    <Button size="small" variant="outlined" color="error" onClick={() => onDeleteMatch(m.id)}>
                      Löschen
                    </Button>
                  </div>
                </>
              )}
            </div>
          ))}
        </div>
      </div>
    </>
  );
};

interface WizardStep2PermissionsProps {
  formData: EventData;
  teams: SelectOption[];
  users: User[];
  onChange: (field: string, value: any) => void;
}

/**
 * Wizard Step 2 for non-game/non-task events: Permissions
 */
export const WizardStep2Permissions: React.FC<WizardStep2PermissionsProps> = (props) => {
  // Pass handleChange for compatibility
  return <PermissionFields {...props} handleChange={props.onChange} />;
};

interface WizardStepDescriptionProps {
  formData: EventData;
  onChange: (field: string, value: any) => void;
}

/**
 * Description step
 */
export const WizardStepDescription: React.FC<WizardStepDescriptionProps> = ({ formData, onChange }) => {
  return (
    <TextField
      label="Beschreibung"
      value={formData.description || ''}
      onChange={e => onChange('description', e.target.value)}
      fullWidth
      margin="normal"
      multiline
      rows={4}
    />
  );
};

interface WizardStepSummaryProps {
  formData: EventData;
  eventTypes: SelectOption[];
  locations: SelectOption[];
  teams: SelectOption[];
  gameTypes: SelectOption[];
  leagues: SelectOption[];
  tournaments: SelectOption[];
  users: User[];
  tournamentMatches: any[];
  isGameEvent: boolean;
  isTaskEvent: boolean;
  isTournament: boolean;
}

/**
 * Summary/review step
 */
export const WizardStepSummary: React.FC<WizardStepSummaryProps> = ({
  formData,
  eventTypes,
  locations,
  teams,
  gameTypes,
  leagues,
  tournaments,
  users,
  tournamentMatches,
  isGameEvent,
  isTaskEvent,
}) => {
  return (
    <>
      <h3>Zusammenfassung</h3>
      <div style={{ marginBottom: 16 }}>
        <strong>Titel:</strong> {formData.title}<br />
        <strong>Typ:</strong> {eventTypes.find(et => et.value === formData.eventType)?.label}<br />
        <strong>Start:</strong> {formData.date} {formData.time}<br />
        <strong>Ende:</strong> {formData.endDate} {formData.endTime}<br />
        <strong>Ort:</strong> {locations.find(l => l.value === formData.locationId)?.label}<br />
        
        {isGameEvent && (
          <>
            {formData.homeTeam && formData.awayTeam && (
              <>
                <strong>Heim-Team:</strong> {teams.find(t => t.value === formData.homeTeam)?.label}<br />
                <strong>Auswärts-Team:</strong> {teams.find(t => t.value === formData.awayTeam)?.label}<br />
              </>
            )}
            {formData.gameType && (
              <>
                <strong>Spiel-Typ:</strong> {gameTypes.find(gt => gt.value === formData.gameType)?.label}<br />
              </>
            )}
            {formData.leagueId && (
              <>
                <strong>Liga:</strong> {leagues.find(l => l.value === formData.leagueId)?.label}<br />
              </>
            )}
            {formData.tournamentId && (
              <>
                <strong>Turnier:</strong> {tournaments.find(tu => tu.value === formData.tournamentId)?.label}<br />
              </>
            )}
            {tournamentMatches && tournamentMatches.length > 0 && (
              <>
                <strong>Begegnungen:</strong>
                <div style={{ marginLeft: 8, marginTop: 6 }}>
                  {tournamentMatches.map((m: any, i: number) => (
                    <div key={m.id} style={{ marginBottom: 4 }}>
                      {`${i + 1}. Runde ${m.round || '?'} - ${m.homeTeamName || m.homeTeam || 'TBD'} vs ${
                        m.awayTeamName || m.awayTeam || 'TBD'
                      }`}
                      {m.scheduledAt ? ` (${m.scheduledAt})` : ''}
                    </div>
                  ))}
                </div>
              </>
            )}
          </>
        )}
        
        {isTaskEvent && (
          <>
            <strong>Benutzer-Rotation:</strong>{' '}
            {users
              .filter(u => (formData.taskRotationUsers || []).includes(u.id.toString()))
              .map(getUserLabel)
              .join(', ')}
            <br />
            <strong>Personen pro Aufgabe:</strong> {formData.taskRotationCount}<br />
            <strong>Wiederkehrend:</strong> {formData.taskIsRecurring ? 'Ja' : 'Nein'}<br />
            {formData.taskIsRecurring && (
              <>
                <strong>Modus:</strong>{' '}
                {formData.taskRecurrenceMode === 'per_match' ? 'Pro Spiel' : 'Regelmäßig'}<br />
                {formData.taskRecurrenceMode === 'classic' && (
                  <>
                    <strong>Frequenz:</strong> {formData.taskFreq}<br />
                    <strong>Intervall:</strong> {formData.taskInterval}<br />
                    {formData.taskFreq === 'WEEKLY' && (
                      <>
                        <strong>Wochentag:</strong> {formData.taskByDay}<br />
                      </>
                    )}
                    {formData.taskFreq === 'MONTHLY' && (
                      <>
                        <strong>Tag des Monats:</strong> {formData.taskByMonthDay}<br />
                      </>
                    )}
                  </>
                )}
                {formData.taskRecurrenceMode === 'per_match' && (
                  <>
                    <strong>Offset zum Spieltag:</strong> {formData.taskOffset}<br />
                  </>
                )}
              </>
            )}
          </>
        )}
        
        {!isGameEvent && !isTaskEvent && (
          <>
            <strong>Berechtigung:</strong> {formData.permissionType}<br />
          </>
        )}
        
        <strong>Beschreibung:</strong> {formData.description}<br />
      </div>
    </>
  );
};
