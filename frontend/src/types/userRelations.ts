export interface UserRelationEditModalProps {
  open: boolean;
  onClose: () => void;
  user: any;
}

export interface AssignmentFormState {
  playerAssignments: Array<{
    id: string;
    type: string;
    permissions: string[];
  }>;
  coachAssignments: Array<{
    id: string;
    type: string;
    permissions: string[];
  }>;
}
