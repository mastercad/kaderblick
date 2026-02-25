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

// ========================
// Step 0: Basic Data
// ========================

interface WizardStep0Props {
  formData: EventData;
  eventTypes: SelectOption[];
  locations: SelectOption[];
  onChange: (field: string, value: any) => void;
}

export const WizardStep0: React.FC<WizardStep0Props> = (props) => {
  return <EventBaseForm {...props} handleChange={props.onChange} />;
};

// ========================
// Step 1: Type-dependent Details
// ========================

interface WizardStep1Props {
  formData: EventData;
  locations: SelectOption[];
  teams: SelectOption[];
  gameTypes: SelectOption[];
  leagues: SelectOption[];
  tournaments: SelectOption[];
  users: User[];
  isMatchEvent: boolean;
  isTournament: boolean;
  isTournamentEventType: boolean;
  isTask: boolean;
  onChange: (field: string, value: any) => void;
  tournamentMatches?: any[];
  onImportOpen?: () => void;
  onManualOpen?: () => void;
  onGeneratorOpen?: () => void;
  onClearMatches?: () => void;
}

export const WizardStep1: React.FC<WizardStep1Props> = ({
  formData,
  locations,
  teams,
  gameTypes,
  leagues,
  tournaments,
  users,
  isMatchEvent,
  isTournament,
  isTournamentEventType,
  isTask,
  onChange,
  tournamentMatches,
  onImportOpen = () => {},
  onManualOpen = () => {},
  onGeneratorOpen = () => {},
  onClearMatches = () => {},
}) => {
  return (
    <>
      {/* Match events: Location, Game fields, Tournament config */}
      {isMatchEvent && (
        <>
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
            isTournamentEventType={isTournamentEventType}
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
                onImportOpen={onImportOpen}
                onManualOpen={onManualOpen}
                onGeneratorOpen={onGeneratorOpen}
                onClearMatches={onClearMatches}
                showOldGeneration={!!formData.tournamentId}
              />
            </>
          )}
        </>
      )}

      {/* Task events */}
      {isTask && (
        <TaskEventFields
          formData={formData}
          users={users}
          handleChange={onChange}
        />
      )}

      {/* Generic events: Permission fields inline */}
      {!isMatchEvent && !isTask && (
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

// ========================
// Step 2a: Tournament Matches
// ========================

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
                <MatchEditForm
                  draft={editingMatchDraft}
                  teams={teams}
                  onChange={setEditingMatchDraft}
                  onSave={onSaveMatch}
                  onCancel={onCancelEdit}
                />
              ) : (
                <MatchDisplay
                  match={m}
                  onEdit={() => onEditMatch(m)}
                  onDelete={() => onDeleteMatch(m.id)}
                />
              )}
            </div>
          ))}
        </div>
      </div>
    </>
  );
};

/** Inline match edit form (extracted for readability) */
const MatchEditForm: React.FC<{
  draft: any;
  teams: SelectOption[];
  onChange: (draft: any) => void;
  onSave: () => void;
  onCancel: () => void;
}> = ({ draft, teams, onChange, onSave, onCancel }) => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
    <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
      <Autocomplete
        options={teams}
        getOptionLabel={(opt: any) => opt.label}
        value={teams.find(t => String(t.value) === String(draft?.homeTeamId)) || null}
        onChange={(_, nv) =>
          onChange((d: any) => ({ ...d, homeTeamId: nv?.value || '', homeTeamName: nv?.label || '' }))
        }
        renderInput={(params) => <TextField {...params} label="Heim-Team" size="small" />}
        sx={{ minWidth: 220 }}
      />
      <Autocomplete
        options={teams}
        getOptionLabel={(opt: any) => opt.label}
        value={teams.find(t => String(t.value) === String(draft?.awayTeamId)) || null}
        onChange={(_, nv) =>
          onChange((d: any) => ({ ...d, awayTeamId: nv?.value || '', awayTeamName: nv?.label || '' }))
        }
        renderInput={(params) => <TextField {...params} label="Auswärts-Team" size="small" />}
        sx={{ minWidth: 220 }}
      />
    </div>
    <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
      <TextField
        label="Runde" size="small"
        value={draft?.round || ''}
        onChange={e => onChange((d: any) => ({ ...d, round: e.target.value }))}
        sx={{ minWidth: 120 }}
      />
      <TextField
        label="Slot" size="small"
        value={draft?.slot || ''}
        onChange={e => onChange((d: any) => ({ ...d, slot: e.target.value }))}
        sx={{ minWidth: 120 }}
      />
      <TextField
        label="Datum/Zeit ISO" size="small"
        value={draft?.scheduledAt || ''}
        onChange={e => onChange((d: any) => ({ ...d, scheduledAt: e.target.value }))}
        sx={{ minWidth: 200 }}
      />
    </div>
    <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
      <Button size="small" variant="contained" color="primary" onClick={onSave}>Speichern</Button>
      <Button size="small" variant="outlined" onClick={onCancel}>Abbrechen</Button>
    </div>
  </div>
);

/** Match display row (extracted for readability) */
const MatchDisplay: React.FC<{
  match: any;
  onEdit: () => void;
  onDelete: () => void;
}> = ({ match, onEdit, onDelete }) => (
  <>
    <div style={{ flex: 1 }}>
      {`Runde ${match.round || '?'} - ${match.homeTeamName || 'TBD'} vs ${match.awayTeamName || 'TBD'}`}
    </div>
    <div style={{ marginLeft: 12, display: 'flex', gap: 8 }}>
      <Button size="small" variant="outlined" onClick={onEdit}>Bearbeiten</Button>
      <Button size="small" variant="outlined" color="error" onClick={onDelete}>Löschen</Button>
    </div>
  </>
);

// ========================
// Step 2b: Permissions (generic events only)
// ========================

interface WizardStep2PermissionsProps {
  formData: EventData;
  teams: SelectOption[];
  users: User[];
  onChange: (field: string, value: any) => void;
}

export const WizardStep2Permissions: React.FC<WizardStep2PermissionsProps> = (props) => {
  return <PermissionFields {...props} handleChange={props.onChange} />;
};

// ========================
// Description Step
// ========================

interface WizardStepDescriptionProps {
  formData: EventData;
  onChange: (field: string, value: any) => void;
}

export const WizardStepDescription: React.FC<WizardStepDescriptionProps> = ({ formData, onChange }) => (
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

// ========================
// Summary Step
// ========================

const GAME_MODE_LABELS: Record<string, string> = {
  'round_robin': 'Jeder gegen Jeden',
  'groups_with_finals': 'Gruppen + Finale',
};

const TOURNAMENT_TYPE_LABELS: Record<string, string> = {
  'indoor_hall': 'Hallenturnier (1 Feld)',
  'normal': 'Normal (mehrere Felder)',
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
  isMatchEvent: boolean;
  isTournament: boolean;
  isTask: boolean;
}

export const WizardStepSummary: React.FC<WizardStepSummaryProps> = ({
  formData, eventTypes, locations, teams, gameTypes, leagues, tournaments, users,
  tournamentMatches, isMatchEvent, isTournament, isTask,
}) => (
  <>
    <h3>Zusammenfassung</h3>
    <div style={{ marginBottom: 16 }}>
      {/* Base info */}
      <strong>Titel:</strong> {formData.title}<br />
      <strong>Typ:</strong> {eventTypes.find(et => et.value === formData.eventType)?.label}<br />
      <strong>Start:</strong> {formData.date} {formData.time}<br />
      <strong>Ende:</strong> {formData.endDate} {formData.endTime}<br />
      <strong>Ort:</strong> {locations.find(l => l.value === formData.locationId)?.label}<br />

      {/* Match event details */}
      {isMatchEvent && (
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
        </>
      )}

      {/* Tournament details */}
      {isTournament && (
        <>
          {formData.tournamentId && (
            <>
              <strong>Turnier:</strong> {tournaments.find(tu => tu.value === formData.tournamentId)?.label}<br />
            </>
          )}
          {formData.tournamentType && (
            <>
              <strong>Turniertyp:</strong> {TOURNAMENT_TYPE_LABELS[formData.tournamentType] || formData.tournamentType}<br />
            </>
          )}
          {formData.tournamentGameMode && (
            <>
              <strong>Spielmodus:</strong> {GAME_MODE_LABELS[formData.tournamentGameMode] || formData.tournamentGameMode}<br />
            </>
          )}
          {formData.tournamentRoundDuration && (
            <>
              <strong>Spieldauer:</strong> {formData.tournamentRoundDuration} Min.<br />
            </>
          )}
          {formData.tournamentBreakTime !== undefined && (
            <>
              <strong>Pausenzeit:</strong> {formData.tournamentBreakTime} Min.<br />
            </>
          )}
          {formData.tournamentGameMode === 'groups_with_finals' && formData.tournamentNumberOfGroups && (
            <>
              <strong>Anzahl Gruppen:</strong> {formData.tournamentNumberOfGroups}<br />
            </>
          )}
          {tournamentMatches && tournamentMatches.length > 0 && (
            <>
              <strong>Begegnungen ({tournamentMatches.length}):</strong>
              <div style={{ marginLeft: 8, marginTop: 6 }}>
                {tournamentMatches.map((m: any, i: number) => (
                  <div key={m.id} style={{ marginBottom: 4 }}>
                    {`${i + 1}. ${m.stage
                      ? m.stage + (m.group && !m.stage.includes('Gr.') ? ` (Gr. ${m.group})` : '')
                      : `Runde ${m.round || '?'}${m.group ? ` (Gr. ${m.group})` : ''}`
                    } - ${m.homeTeamName || m.homeTeam || 'TBD'} vs ${
                      m.awayTeamName || m.awayTeam || 'TBD'
                    }`}
                    {m.scheduledAt ? ` (${new Date(m.scheduledAt).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })})` : ''}
                  </div>
                ))}
              </div>
            </>
          )}
        </>
      )}

      {/* Task details */}
      {isTask && (
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
                    <><strong>Wochentag:</strong> {formData.taskByDay}<br /></>
                  )}
                  {formData.taskFreq === 'MONTHLY' && (
                    <><strong>Tag des Monats:</strong> {formData.taskByMonthDay}<br /></>
                  )}
                </>
              )}
              {formData.taskRecurrenceMode === 'per_match' && (
                <><strong>Offset zum Spieltag:</strong> {formData.taskOffset}<br /></>
              )}
            </>
          )}
        </>
      )}

      {/* Generic events: permissions */}
      {!isMatchEvent && !isTask && (
        <><strong>Berechtigung:</strong> {formData.permissionType}<br /></>
      )}

      <strong>Beschreibung:</strong> {formData.description}<br />
    </div>
  </>
);
