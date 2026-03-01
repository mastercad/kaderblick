// Re-export from userRelations for backwards compatibility
export type { UserRelationEditModalProps, AssignmentFormState } from './userRelations';

export interface UserRelation {
  id: number;
  relationType?: {
    id: number;
    name?: string;
  } | null;
  relatedUser?: {
    id: number;
    firstName?: string;
    lastName?: string;
    fullName?: string;
  } | null;
}
