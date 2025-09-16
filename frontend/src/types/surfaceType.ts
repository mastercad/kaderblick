import { Permissions } from './permissions'

export type SurfaceType = {
  id: number;
  name: string;
  description: string;
  permissions: Permissions;
};
