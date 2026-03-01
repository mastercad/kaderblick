import { Permissions } from './permissions';

export interface PlayerLicenseAssignment {
  id: number;
  license?: {
    id: number;
    name: string;
    description?: string;
  };
  startDate?: string;
  endDate?: string;
  active?: boolean;
  permissions?: Permissions;
}
