import { Permissions } from './permissions';

export interface Position {
  id: number;
  name: string;
  shortName: string;
  description: string;
  permissions?: Permissions;
}
