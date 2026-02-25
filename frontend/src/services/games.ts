import { apiJson } from '../utils/api';
import { Game, GameEvent, GameEventType, Player, SubstitutionReason, GameWithScore, TournamentOverview, TournamentDetail } from '../types/games';

export interface GamesOverviewData {
  running_games: Game[];
  upcoming_games: Game[];
  finished_games: GameWithScore[];
  tournaments: TournamentOverview[];
  userTeamIds: number[];
}

// Spiele-Übersicht laden
export async function fetchGamesOverview(): Promise<GamesOverviewData> {
  // Da das Backend noch Twig verwendet, verwenden wir eine temporäre API-Route
  // oder simulieren die Daten bis eine echte API verfügbar ist
  return apiJson<GamesOverviewData>('/api/games/overview');
}

// Einzelnes Spiel mit Details laden
export async function fetchGameDetails(gameId: number): Promise<{
  game: Game;
  gameEvents: GameEvent[];
  homeScore: number | null;
  awayScore: number | null;
}> {
  return apiJson(`/api/games/${gameId}/details`);
}

// Game Events für ein Spiel laden
export async function fetchGameEvents(gameId: number): Promise<GameEvent[]> {
  return apiJson<GameEvent[]>(`/api/game/${gameId}/events`);
}

// Event Types laden
export async function fetchGameEventTypes(): Promise<any> {
  return apiJson<any>('/api/game-event-types');
}

// Spieler für Teams laden
export async function fetchPlayersForTeams(teamIds: number[]): Promise<Player[]> {
  const teamParams = teamIds.map(id => `teams[]=${id}`).join('&');
  return apiJson<Player[]>(`/api/players/active?${teamParams}`);
}

// Substitution Reasons laden
export async function fetchSubstitutionReasons(): Promise<SubstitutionReason[]> {
  return apiJson<SubstitutionReason[]>('/api/substitution-reasons');
}

// Turnier-Details laden
export async function fetchTournamentDetails(tournamentId: number): Promise<TournamentDetail> {
  return apiJson<TournamentDetail>(`/api/tournaments/${tournamentId}`);
}

// Neues Game Event erstellen
export async function createGameEvent(gameId: number, eventData: {
  eventType: number;
  player?: number;
  relatedPlayer?: number;
  minute: string;
  description?: string;
  reason?: number;
}): Promise<{ success: boolean }> {
  return apiJson(`/api/game/${gameId}/event`, {
    method: 'POST',
    body: eventData
  });
}

// Game Event aktualisieren
export async function updateGameEvent(gameId: number, eventId: number, eventData: {
  eventType?: number;
  player?: number;
  relatedPlayer?: number;
  minute?: string;
  description?: string;
  reason?: number;
}): Promise<{ success: boolean }> {
  return apiJson(`/api/game/${gameId}/event/${eventId}`, {
    method: 'PUT',
    body: eventData
  });
}

// Game Event löschen
export async function deleteGameEvent(gameId: number, eventId: number): Promise<{ success: boolean }> {
  return apiJson(`/api/game/${gameId}/event/${eventId}`, {
    method: 'DELETE'
  });
}

// Fussball.de Sync
export async function syncFussballDe(gameId: number): Promise<{ success: boolean }> {
  return apiJson(`/api/game/${gameId}/sync-fussballde`, {
    method: 'POST'
  });
}
