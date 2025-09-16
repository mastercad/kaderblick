import { Permissions }  from './permissions';

export interface GameEventType {
  id: number;
  name: string;
  code: string;
  color: string;
  icon: string;
  isSystem: boolean;
  permissions?: Permissions;
}
