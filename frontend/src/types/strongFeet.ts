import { Permissions }  from './permissions';

export interface StrongFeet {
  id: number;
  name: string;
  code: string;
  permissions?: Permissions;
}
