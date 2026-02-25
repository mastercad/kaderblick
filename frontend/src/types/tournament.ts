/**
 * Tournament Type Definitions
 */

export type TournamentGameMode = 'round_robin' | 'groups_with_finals';
export type TournamentType = 'normal' | 'indoor_hall';

export interface TournamentSettings {
  /** Duration of each match in minutes (default: 10) */
  roundDuration: number;
  
  /** Break time between matches in minutes (default: 2) */
  breakTime: number;
  
  /** Game mode: round_robin (everyone vs everyone) or groups_with_finals (group phase + finals) */
  gameMode: TournamentGameMode;
  
  /** Tournament type: normal (mehrere Felder parallel) or indoor_hall (ein Feld sequenziell) */
  tournamentType: TournamentType;
  
  /** Number of groups (only relevant for groups_with_finals mode) */
  numberOfGroups?: number;
  
  /** Teams per group (only relevant for groups_with_finals mode) */
  teamsPerGroup?: number;

  matches?: GeneratedMatch[];
}

export interface TournamentTeam {
  id?: number | string;
  teamId: number | string;
  teamName?: string;
  groupKey?: string;
  rank?: number;
}

export interface TournamentMatch {
  id?: number | string;
  round: number;
  slot?: number;
  homeTeamId?: number | string;
  awayTeamId?: number | string;
  homeTeamName?: string;
  awayTeamName?: string;
  group?: string;
  scheduledAt?: string;
  status?: string;
}

export interface Tournament {
  id?: number | string;
  name: string;
  type: string;
  startAt?: string;
  endAt?: string;
  settings?: TournamentSettings;
  createdBy?: number | string;
  teams?: TournamentTeam[];
  matches?: TournamentMatch[];
}

/**
 * Options for generating tournament matches
 */
export interface GenerateTournamentMatchesOptions {
  teams: { value: string; label: string }[];
  gameMode: TournamentGameMode;
  tournamentType: TournamentType;
  roundDuration: number;
  breakTime: number;
  startTime: string; // ISO datetime string
  numberOfGroups?: number;
}

export interface GeneratedMatch {
  round: number;
  slot: number;
  homeTeamId: string;
  awayTeamId: string;
  homeTeamName: string;
  awayTeamName: string;
  group?: string;
  scheduledAt: string;
  stage?: string; // KO-Runde: "Viertelfinale", "Halbfinale", "Finale", "Spiel um Platz 3"
}
