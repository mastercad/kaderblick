import { License } from './coachLicense';
import { Coach } from './coach';

export type LicenseAssignment = {
    id: number;
    coach: Coach;
    license: License;
    startDate: string;
    endDate: string;
    active: boolean;
};
