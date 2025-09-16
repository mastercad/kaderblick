import { Coach } from './coach';
import { Club } from './club';

export type CoachClubAssignment = {
    id: number;
    startDate: string;
    endDate?: string;
    club: Club;
    active: boolean;
    coach: Coach;
};
