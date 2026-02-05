export interface User {
  id: string | number;
  fullName?: string;
  firstName?: string;
  lastName?: string;
}

export interface EventData {
  title?: string;
  date?: string;
  time?: string;
  endDate?: string;
  endTime?: string;
  eventType?: string;
  locationId?: string;
  description?: string;
  homeTeam?: string;
  awayTeam?: string;
  gameType?: string;
  leagueId?: string;
  permissionType?: string;
  permissionTeams?: string[];
  permissionClubs?: string[];
  permissionUsers?: string[];
  // Task-bezogene Felder
  task?: {
    id?: number;
    isRecurring?: boolean;
    recurrenceMode?: string;
    recurrenceRule?: string | null;
    rotationUsers?: { id: number; fullName: string }[];
    rotationCount?: number;
    offset?: number;
  };
  taskIsRecurring?: boolean;
  taskRecurrenceMode?: string;
  taskFreq?: string;
  taskInterval?: number;
  taskByDay?: string;
  taskByMonthDay?: number;
  taskRecurrenceRule?: string;
  taskRotationUsers?: string[];
  taskRotationCount?: number;
  taskOffset?: number;
  // Tournament fields
  tournamentId?: string;
  tournamentMatchId?: string;
  pendingTournamentMatches?: any[];
  // Tournament settings
  tournamentRoundDuration?: number;
  tournamentBreakTime?: number;
  tournamentGameMode?: 'round_robin' | 'groups_with_finals';
  tournamentType?: 'indoor_hall' | 'normal';
  tournamentNumberOfGroups?: number;
  // Lineup fields
  enableLineups?: boolean;
  homeLineup?: string;
  awayLineup?: string;
}

export interface SelectOption {
  value: string;
  label: string;
}
