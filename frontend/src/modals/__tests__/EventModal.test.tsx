import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import '@testing-library/jest-dom';
import { EventModal } from '../EventModal';

// MUI-Komponenten mocken und Props filtern
const filterProps = (props: any) => {
  const { children } = props;
  return { children };
};
jest.mock('@mui/material/Dialog', () => (props: any) => <div data-testid="Dialog">{filterProps(props).children}</div>);
jest.mock('@mui/material/DialogTitle', () => (props: any) => <div data-testid="DialogTitle">{filterProps(props).children}</div>);
jest.mock('@mui/material/DialogContent', () => (props: any) => <div data-testid="DialogContent">{filterProps(props).children}</div>);
jest.mock('@mui/material/DialogActions', () => (props: any) => <div data-testid="DialogActions">{filterProps(props).children}</div>);
jest.mock('@mui/material/Button', () => (props: any) => <button {...props}>{props.children}</button>);
jest.mock('@mui/material/TextField', () => (props: any) => <input {...props} data-testid={props.label} />);
jest.mock('@mui/material/Select', () => (props: any) => <select {...props}>{props.children}</select>);
jest.mock('@mui/material/MenuItem', () => (props: any) => <option {...props}>{props.children}</option>);
jest.mock('@mui/material/InputLabel', () => (props: any) => <label {...props}>{props.children}</label>);
jest.mock('@mui/material/FormControl', () => (props: any) => <div>{props.children}</div>);
jest.mock('@mui/material/Autocomplete', () => (props: any) => <div data-testid="Autocomplete">{props.renderInput({})}</div>);

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
