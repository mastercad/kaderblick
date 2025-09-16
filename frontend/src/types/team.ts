import { AgeGroup } from './ageGroup';
import { League } from './league';

export type Team = {
    id: number;
    name: string;
    ageGroup: AgeGroup;
    league: League;
    fussball_de_id: string;
    fussball_de_url: string;
};
