import { Team } from './team';
import { Coach } from './coach';
import { CoachTeamAssignmentType } from './coachTeamAssignmentType';

export type CoachTeamAssignment = {
    id: number;
    startDate: string;
    endDate?: string;
    type: CoachTeamAssignmentType;
    active: boolean;
    coach: Coach;
    team: Team;
};
