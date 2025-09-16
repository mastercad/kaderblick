import { Player } from "./player";
import { Nationality } from "./nationality";

export type PlayerNationalityAssignment = {
    id: number;
    startDate: string;
    endDate?: string;
    active: boolean;
    coach: Player;
    nationality: Nationality;
};
