import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import '@testing-library/jest-dom';
import { EventModal } from '../EventModal';

// Mock BaseModal to avoid MUI theme/useMediaQuery issues
jest.mock('../BaseModal', () => ({
  __esModule: true,
  default: ({ open, title, children, actions }: any) => open ? (
    <div data-testid="Dialog">
      <div data-testid="DialogTitle">{title}</div>
      <div data-testid="DialogContent">{children}</div>
      <div data-testid="DialogActions">{actions}</div>
    </div>
  ) : null,
}));

// Mock hooks that make API calls
jest.mock('../../hooks/useEventData', () => ({
  useTournamentMatches: () => ({ tournamentMatches: [], setTournamentMatches: jest.fn() }),
  useLeagues: () => [],
  useReloadTournamentMatches: () => jest.fn(),
}));

// MUI-Komponenten mocken
jest.mock('@mui/material/Button', () => (props: any) => <button {...props}>{props.children}</button>);
jest.mock('@mui/material/TextField', () => (props: any) => <input {...props} data-testid={props.label} />);
jest.mock('@mui/material/Select', () => (props: any) => <select {...props}>{props.children}</select>);
jest.mock('@mui/material/MenuItem', () => (props: any) => <option {...props}>{props.children}</option>);
jest.mock('@mui/material/InputLabel', () => (props: any) => <label {...props}>{props.children}</label>);
jest.mock('@mui/material/FormControl', () => (props: any) => <div>{props.children}</div>);
jest.mock('@mui/material/Autocomplete', () => (props: any) => <div data-testid="Autocomplete">{props.renderInput({})}</div>);

// Mock sub-components that might import problematic dependencies
jest.mock('../ImportMatchesDialog', () => () => null);
jest.mock('../ManualMatchesEditor', () => () => null);
jest.mock('../TournamentMatchGeneratorDialog', () => () => null);
jest.mock('../../components/EventModal/TaskEventFields', () => ({ TaskEventFields: () => null }));
jest.mock('../../components/EventModal/PermissionFields', () => ({ PermissionFields: () => null }));
jest.mock('../../components/EventModal/TournamentFields', () => ({
  TournamentConfig: () => null,
  TournamentMatchesManagement: () => null,
  TournamentSelection: () => null,
}));
jest.mock('../../components/EventModal/EventWizard', () => ({ EventWizard: () => null }));

// Logging unterdrücken
beforeAll(() => {
  jest.spyOn(console, 'log').mockImplementation(() => {});
  jest.spyOn(console, 'error').mockImplementation(() => {});
});
afterAll(() => {
  (console.log as jest.Mock).mockRestore();
  (console.error as jest.Mock).mockRestore();
});

const eventTypes = [
  { value: 'training', label: 'Training' },
  { value: 'spiel', label: 'Spiel' },
];
const teams = [
  { value: 'team1', label: 'Team 1' },
  { value: 'team2', label: 'Team 2' },
];
const gameTypes = [
  { value: 'liga', label: 'Liga' },
  { value: 'freundschaft', label: 'Freundschaft' },
];
const locations = [
  { value: 'loc1', label: 'Sportplatz 1' },
  { value: 'loc2', label: 'Sportplatz 2' },
];

const defaultEvent = {
  title: 'Testevent',
  date: '2025-09-26',
  time: '18:00',
  endDate: '2025-09-26',
  endTime: '20:00',
  eventType: 'training',
  locationId: 'loc1',
  description: 'Beschreibung',
};

describe('EventModal', () => {
  const onChange = jest.fn();
  const onClose = jest.fn();
  const onSave = jest.fn();
  const onDelete = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renders modal with event fields', async () => {
    await act(async () => {
      render(
        <EventModal
          open={true}
          onClose={onClose}
          onSave={onSave}
          event={defaultEvent}
          eventTypes={eventTypes}
          teams={teams}
          gameTypes={gameTypes}
          locations={locations}
          onChange={onChange}
        />
      );
    });
    expect(screen.getByTestId('Dialog')).toBeInTheDocument();
    expect(screen.getByTestId('DialogTitle')).toHaveTextContent('Event verwalten');
    expect(screen.getByTestId('Titel *')).toBeInTheDocument();
    expect(screen.getByTestId('Datum *')).toBeInTheDocument();
    expect(screen.getByTestId('Ort')).toBeInTheDocument();
    expect(screen.getByTestId('Beschreibung')).toBeInTheDocument();
  });

  it('calls onClose when Abbrechen button is clicked', async () => {
    await act(async () => {
      render(
        <EventModal
          open={true}
          onClose={onClose}
          onSave={onSave}
          event={defaultEvent}
          eventTypes={eventTypes}
          teams={teams}
          gameTypes={gameTypes}
          locations={locations}
          onChange={onChange}
        />
      );
    });
    fireEvent.click(screen.getByText('Abbrechen'));
    expect(onClose).toHaveBeenCalled();
  });

  it('calls onSave when Speichern button is clicked', async () => {
    await act(async () => {
      render(
        <EventModal
          open={true}
          onClose={onClose}
          onSave={onSave}
          event={defaultEvent}
          eventTypes={eventTypes}
          teams={teams}
          gameTypes={gameTypes}
          locations={locations}
          onChange={onChange}
        />
      );
    });
    fireEvent.click(screen.getByText('Speichern'));
    expect(onSave).toHaveBeenCalled();
  });

  it('calls onDelete when Löschen button is clicked', async () => {
    await act(async () => {
      render(
        <EventModal
          open={true}
          onClose={onClose}
          onSave={onSave}
          onDelete={onDelete}
          showDelete={true}
          event={defaultEvent}
          eventTypes={eventTypes}
          teams={teams}
          gameTypes={gameTypes}
          locations={locations}
          onChange={onChange}
        />
      );
    });
    fireEvent.click(screen.getByText('Löschen'));
    expect(onDelete).toHaveBeenCalled();
  });

  it('shows game fields when eventType is Spiel', async () => {
    await act(async () => {
      render(
        <EventModal
          open={true}
          onClose={onClose}
          onSave={onSave}
          event={{ ...defaultEvent, eventType: 'spiel' }}
          eventTypes={eventTypes}
          teams={teams}
          gameTypes={gameTypes}
          locations={locations}
          onChange={onChange}
        />
      );
    });
    expect(screen.getByText('Heim-Team *')).toBeInTheDocument();
    expect(screen.getByText('Auswärts-Team *')).toBeInTheDocument();
    expect(screen.getByText('Spiel-Typ')).toBeInTheDocument();
  });

  it('disables buttons when loading', async () => {
    await act(async () => {
      render(
        <EventModal
          open={true}
          onClose={onClose}
          onSave={onSave}
          onDelete={onDelete}
          showDelete={true}
          event={defaultEvent}
          eventTypes={eventTypes}
          teams={teams}
          gameTypes={gameTypes}
          locations={locations}
          onChange={onChange}
          loading={true}
        />
      );
    });
    expect(screen.getByText('Abbrechen')).toBeDisabled();
    expect(screen.getByText('Löschen')).toBeDisabled();
    expect(screen.getByText('Wird gespeichert...')).toBeDisabled();
  });

  it('calls onChange when input changes', async () => {
    await act(async () => {
      render(
        <EventModal
          open={true}
          onClose={onClose}
          onSave={onSave}
          event={defaultEvent}
          eventTypes={eventTypes}
          teams={teams}
          gameTypes={gameTypes}
          locations={locations}
          onChange={onChange}
        />
      );
    });
    fireEvent.change(screen.getByTestId('Titel *'), { target: { value: 'Neuer Titel' } });
    expect(onChange).toHaveBeenCalledWith('title', 'Neuer Titel');
  });
});
