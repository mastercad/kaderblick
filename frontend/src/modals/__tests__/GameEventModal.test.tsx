import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import '@testing-library/jest-dom';
import { GameEventModal } from '../GameEventModal';
import type { Game } from '../../types/games';

// ── Mocks ────────────────────────────────────────────────────────────────────

// Vereinfachtes BaseModal damit MUI-Theming entfällt
jest.mock('../BaseModal', () => ({
  __esModule: true,
  default: ({ open, title, children, actions }: any) =>
    open ? (
      <div data-testid="Dialog">
        <div data-testid="DialogTitle">{title}</div>
        <div data-testid="DialogContent">{children}</div>
        <div data-testid="DialogActions">{actions}</div>
      </div>
    ) : null,
}));

// Services-Mock
jest.mock('../../services/games', () => ({
  fetchGameEventTypes: jest.fn(),
  fetchSubstitutionReasons: jest.fn(),
  fetchGameSquad: jest.fn(),
  createGameEvent: jest.fn(),
  updateGameEvent: jest.fn(),
}));

// apiJson direkt im Modal (für Spieler-Fallback via /api/teams/{id}/players)
jest.mock('../../utils/api', () => ({
  apiJson: jest.fn(),
  getApiErrorMessage: jest.fn(() => 'Ein Fehler ist aufgetreten'),
}));

import {
  fetchGameEventTypes,
  fetchSubstitutionReasons,
  fetchGameSquad,
} from '../../services/games';
import { apiJson } from '../../utils/api';

// ── Fixture-Daten ────────────────────────────────────────────────────────────

const HOME_TEAM_ID = 1;
const AWAY_TEAM_ID = 2;

const mockGame: Game = {
  id: 100,
  homeTeam: { id: HOME_TEAM_ID, name: 'FC Home' },
  awayTeam: { id: AWAY_TEAM_ID, name: 'SC Away' },
  halfDuration: 45,
  calendarEvent: {
    id: 50,
    startDate: new Date(Date.now() - 20 * 60 * 1000).toISOString(),
    endDate: new Date(Date.now() + 70 * 60 * 1000).toISOString(),
    calendarEventType: { id: 1, name: 'Spiel' },
  },
};

/** Basiert auf dem Shape, das der Controller zurückgibt */
const existingEventWithHomeTeam: any = {
  id: 1,
  teamId: HOME_TEAM_ID,
  typeId: 0,
  minute: '10',
  description: '',
};

const defaultProps = {
  open: true,
  onClose: jest.fn(),
  onSuccess: jest.fn(),
  gameId: 100,
  game: mockGame,
  existingEvent: null as any,
};

// ── Hilfsfunktion ─────────────────────────────────────────────────────────────

function setupDefaultMocks() {
  (fetchGameEventTypes as jest.Mock).mockResolvedValue([]);
  (fetchSubstitutionReasons as jest.Mock).mockResolvedValue([]);
  (apiJson as jest.Mock).mockResolvedValue([]);
}

// ── Tests ─────────────────────────────────────────────────────────────────────

describe('GameEventModal – Squad-Chip', () => {
  beforeAll(() => {
    jest.spyOn(console, 'error').mockImplementation(() => {});
    jest.spyOn(console, 'warn').mockImplementation(() => {});
  });
  afterAll(() => {
    (console.error as jest.Mock).mockRestore();
    (console.warn as jest.Mock).mockRestore();
  });

  beforeEach(() => {
    jest.clearAllMocks();
    setupDefaultMocks();
  });

  // ── Kein ChipAnzeige ohne Participation-Daten ─────────────────────────────

  it('zeigt keinen Squad-Chip wenn hasParticipationData: false', async () => {
    (fetchGameSquad as jest.Mock).mockResolvedValue({
      squad: [],
      hasParticipationData: false,
    });

    await act(async () => {
      render(<GameEventModal {...defaultProps} existingEvent={existingEventWithHomeTeam} />);
    });

    await waitFor(() => {
      expect(fetchGameSquad).toHaveBeenCalledWith(100);
    });

    // Kein Chip – weder "zugesagt" noch "Keine Zusagen"
    expect(screen.queryByText(/zugesagt/i)).not.toBeInTheDocument();
    expect(screen.queryByText('Keine Zusagen')).not.toBeInTheDocument();
  });

  // ── "Keine Zusagen"-Chip wenn niemand erscheint ───────────────────────────

  it('zeigt "Keine Zusagen"-Chip wenn Teilnahmen existieren aber kein Spieler im Squad', async () => {
    (fetchGameSquad as jest.Mock).mockResolvedValue({
      squad: [], // kein Spieler dieser Team
      hasParticipationData: true,
    });

    await act(async () => {
      render(<GameEventModal {...defaultProps} existingEvent={existingEventWithHomeTeam} />);
    });

    await waitFor(() => {
      expect(screen.getByText('Keine Zusagen')).toBeInTheDocument();
    });
  });

  // ── "X zugesagt"-Chip bei vorhandenen Squad-Spielern ─────────────────────

  it('zeigt "X zugesagt"-Chip wenn Squad-Spieler vorhanden', async () => {
    (fetchGameSquad as jest.Mock).mockResolvedValue({
      squad: [
        { id: 1, fullName: 'Max Muster', shirtNumber: 7, teamId: HOME_TEAM_ID },
        { id: 2, fullName: 'Lisa Lauf', shirtNumber: 9, teamId: HOME_TEAM_ID },
      ],
      hasParticipationData: true,
    });

    await act(async () => {
      render(<GameEventModal {...defaultProps} existingEvent={existingEventWithHomeTeam} />);
    });

    await waitFor(() => {
      expect(screen.getByText('2 zugesagt')).toBeInTheDocument();
    });
  });

  // ── Chip-Klick wechselt zu "Alle Spieler" ────────────────────────────────

  it('wechselt nach Klick auf Chip zu "Alle Spieler" und lädt alle Teamspieler', async () => {
    (fetchGameSquad as jest.Mock).mockResolvedValue({
      squad: [
        { id: 1, fullName: 'Max Muster', shirtNumber: 7, teamId: HOME_TEAM_ID },
      ],
      hasParticipationData: true,
    });

    const allTeamPlayers = [
      { id: 1, fullName: 'Max Muster', shirtNumber: 7 },
      { id: 3, fullName: 'Karl Kühn', shirtNumber: 11 },
    ];
    (apiJson as jest.Mock).mockImplementation((url: string) => {
      if (url.includes(`/api/teams/${HOME_TEAM_ID}/players`)) {
        return Promise.resolve(allTeamPlayers);
      }
      return Promise.resolve([]);
    });

    await act(async () => {
      render(<GameEventModal {...defaultProps} existingEvent={existingEventWithHomeTeam} />);
    });

    // Warte bis Squad-Chip erscheint
    await waitFor(() => {
      expect(screen.getByText('1 zugesagt')).toBeInTheDocument();
    });

    // Klick auf den Chip
    await act(async () => {
      fireEvent.click(screen.getByText('1 zugesagt'));
    });

    // Chip-Label wechselt zu "Alle Spieler"
    await waitFor(() => {
      expect(screen.getByText('Alle Spieler')).toBeInTheDocument();
    });

    // apiJson für Teamspieler-Fallback wurde aufgerufen
    expect(apiJson).toHaveBeenCalledWith(`/api/teams/${HOME_TEAM_ID}/players`);
  });

  // ── fetchGameSquad wird mit korrekter gameId aufgerufen ──────────────────

  it('ruft fetchGameSquad mit der gameId der Props auf', async () => {
    (fetchGameSquad as jest.Mock).mockResolvedValue({
      squad: [],
      hasParticipationData: false,
    });

    await act(async () => {
      render(<GameEventModal {...defaultProps} gameId={42} existingEvent={null} />);
    });

    await waitFor(() => {
      expect(fetchGameSquad).toHaveBeenCalledWith(42);
    });
  });

  // ── Kein Chip wenn kein Team ausgewählt ──────────────────────────────────

  it('zeigt keinen Squad-Chip wenn kein Team ausgewählt (kein existingEvent)', async () => {
    (fetchGameSquad as jest.Mock).mockResolvedValue({
      squad: [{ id: 1, fullName: 'Max Muster', shirtNumber: 7, teamId: HOME_TEAM_ID }],
      hasParticipationData: true,
    });

    await act(async () => {
      // Kein existingEvent → kein Team vorausgewählt
      render(<GameEventModal {...defaultProps} existingEvent={null} />);
    });

    await waitFor(() => {
      expect(fetchGameSquad).toHaveBeenCalled();
    });

    // Kein Team → Chip wird nicht gerendert (formData.team ist '')
    expect(screen.queryByText(/zugesagt/i)).not.toBeInTheDocument();
    expect(screen.queryByText('Keine Zusagen')).not.toBeInTheDocument();
  });
});
