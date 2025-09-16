export interface Team {
  id: number;
  name: string;
}

export interface Location {
  id: number;
  name: string;
  address?: string;
  longitude?: number;
  latitude?: number;
}

export interface GameType {
  id: number;
  name: string;
}

export interface CalendarEventType {
  id: number;
  name: string;
}

export interface CalendarEvent {
  id: number;
  startDate: string;
  endDate: string;
  calendarEventType: CalendarEventType;
}

export interface Game {
  id: number;
  homeTeam: Team;
  awayTeam: Team;
  location?: Location;
  gameType?: GameType;
  calendarEvent?: CalendarEvent;
  fussballDeUrl?: string;
}

export interface GameEventType {
  id: number;
  name: string;
  code: string;
  color?: string;
  icon?: string;
}

export interface Player {
  id: number;
  firstName: string;
  lastName: string;
  jerseyNumber?: number;
}

export interface SubstitutionReason {
  id: number;
  name: string;
}

export interface GameEvent {
  id: number;
  game: Game;
  gameEventType: GameEventType;
  player?: Player;
  relatedPlayer?: Player;
  team?: Team;
  timestamp: string;
  description?: string;
  teamId?: number;
  playerId?: number;
  typeId?: number;
  relatedPlayerId?: number;
  minute?: number;
  reason?: string;
}

export interface GameWithScore {
  game: Game;
  homeScore: number | null;
  awayScore: number | null;
}

export interface Video {
  id: number;
  name: string;
  url: string;
  filePath?: string;
  gameStart: number;
  sort: number;
  length: number;
  type?: string;
  camera?: {
    id: number;
    name: string;
  };
}

export interface YoutubeLinks {
  id: number;
  name: string;
  url: string;
  filePath?: string;
  gameStart: number;
  sort: number;
  length: number;
  type?: string;
  camera?: {
    id: number;
    name: string;
  };
}
