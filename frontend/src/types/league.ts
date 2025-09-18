import { Permissions } from './permissions'

export type League = {
    id: number;
    name: string;
    permissions?: Permissions;
};
