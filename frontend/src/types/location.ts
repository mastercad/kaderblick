import { SurfaceType } from './surfaceType';
import { Permissions } from '../types/permissions';

export type Location = {
  id: number;
  name: string;
  latitude: number;
  longitude: number;
  address: string;
  city: string;
  capacity: number;
  hasFloodlight?: boolean;
  facilities?: string;
  permissions: Permissions;
  surfaceType?: SurfaceType;
  surfaceTypeId: number;
};