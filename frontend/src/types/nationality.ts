import { Permissions } from './permissions';

export type Nationality = {
    id: number;
    name: string;
    isoCode: string;
    permissions?: Permissions;
};
