import { Coach } from './coach';
import { CoachLicense } from './coachLicense';

export type CoachLicenseAssignment = {
    id: number;
    startDate: string;
    endDate?: string;
    license: CoachLicense;
    active: boolean;
    coach: Coach;
};
