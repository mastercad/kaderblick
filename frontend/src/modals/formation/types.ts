// ─── Shared type definitions for the Formation editor ─────────────────────────

export interface Player {
  id: number;
  name: string;
  shirtNumber?: string | number;
}

export interface Team {
  id: number;
  name: string;
}

export interface FormationType {
  id?: number;
  name: string;
  cssClass?: string;
  backgroundPath?: string;
}

export interface PlayerData {
  id: number;
  x: number;
  y: number;
  number: string | number;
  name: string;
  playerId?: number | null;
  isRealPlayer?: boolean;
  position?: string;
}

export interface FormationData {
  code?: string;
  players?: PlayerData[];
  bench?: PlayerData[];
  notes?: string;
}

export interface Formation {
  id: number;
  name: string;
  formationType: FormationType;
  formationData: FormationData;
}

export interface FormationEditModalProps {
  open: boolean;
  formationId: number | null;
  onClose: () => void;
  onSaved?: (formation: Formation) => void;
}
