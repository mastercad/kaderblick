import { Player } from './player';
import { Club } from './club';

export type PlayerClubAssignment = {
    id: number;
    startDate: string;
    endDate?: string;
    club: Club;
    active: boolean;
    player: Player;
};
