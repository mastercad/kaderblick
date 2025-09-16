import { Permissions } from './permissions';
import { CoachLicenseAssignment } from './coachLicenseAssignment';
import { CoachTeamAssignment } from './coachTeamAssignment';
import { CoachNationalityAssignment } from './coachNationalityAssignment';
import { CoachClubAssignment } from './coachClubAssignment';
import { UserRelation } from './userRelation'

export interface Coach {
    id: number;
    firstName: string;
    lastName: string;
    email: string;
    birthDate: string;
    profilePicturePath: string;
    clubAssignments: CoachClubAssignment[];
    licenseAssignments: CoachLicenseAssignment[];
    nationalityAssignments: CoachNationalityAssignment[];
    teamAssignments: CoachTeamAssignment[];
    userRelations: UserRelation[];
    permissions: Permissions;
}
