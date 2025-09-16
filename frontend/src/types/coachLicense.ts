import { Permissions } from './permissions'

export type CoachLicense = {
    id: number;
    name: string;
    description: string;
    countryCode: string;
    active: boolean;
    permissions?: Permissions;
};
