import { Coach } from "./coach";
import { Nationality } from "./nationality";

export type CoachNationalityAssignment = {
    id: number;
    startDate: string;
    endDate?: string;
    active: boolean;
    coach: Coach;
    nationality: Nationality;
};
