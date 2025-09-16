export interface Team {
  id: number;
  name: string;
}

export interface Player {
  id: number;
  fullName: string;
  firstName: string;
  lastName: string;
  shirtNumber?: number;
}

export interface EventType {
  id: number;
  name: string;
}

export interface FieldAlias {
  label: string;
  entity: string;
  field: string;
  type: string;
  subfield?: string;
}

export interface ReportConfig {
  diagramType: 'bar' | 'line' | 'pie';
  xField: string;
  yField: string;
  groupBy: string[];
  filters: {
    dateFrom?: string;
    dateTo?: string;
    team?: string;
    player?: string;
    eventType?: string;
  };
}

export interface Report {
  id?: number;
  name: string;
  description?: string;
  isTemplate?: boolean;
  config: ReportConfig;
}

export interface ReportBuilderData {
  teams: Team[];
  players: Player[];
  eventTypes: EventType[];
  fieldAliases: Record<string, FieldAlias>;
  availableDates: string[];
  minDate: string;
  maxDate: string;
}
