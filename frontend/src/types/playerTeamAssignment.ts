import { Team } from './team';
import { Player } from './player';
import { PlayerTeamAssignmentType } from './playerTeamAssignmentType';

export type PlayerTeamAssignment = {
    id: number;
    startDate: string;
    endDate?: string;
    shirtNumber: number;
    type: PlayerTeamAssignmentType;
    active: boolean;
    coach: Player;
    team: Team;
};
