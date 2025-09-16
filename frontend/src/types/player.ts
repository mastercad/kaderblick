import { StrongFeet } from './strongFeet';
import { Position } from './position';
import { PlayerLicenseAssignment } from './playerLicenseAssignment';
import { PlayerTeamAssignment } from './playerTeamAssignment';
import { PlayerNationalityAssignment } from './playerNationalityAssignment';
import { PlayerClubAssignment } from './playerClubAssignment';
import { Permissions } from './permissions';

export type Player = {
    id: number;
    fullName: string;
    firstName: string;
    lastName: string;
    strongFeet: StrongFeet;
    mainPosition: Position;
    alternatePositions: Position[];
    birthDate: string;
    height: number;
    weight: number;
    email: string;
    fussballDeId: string;
    fussballDeUrl: string;
    clubAssignments: PlayerClubAssignment[];
    licenseAssignments: PlayerLicenseAssignment[];
    nationalityAssignments: PlayerNationalityAssignment[];
    teamAssignments: PlayerTeamAssignment[];
    position: string;
    permissions: Permissions;
};
