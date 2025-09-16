import { Permissions } from './permissions';

export type AgeGroup = {
    id: number;
    code: string;
    name: string;
    englishName: string;
    minAge: number;
    maxAge: number;
    referenceDate: string;
    description: string;
    permissions: Permissions;
};
