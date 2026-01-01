export interface Camera {
  id: number;
  name: string;
  createdAt?: string;
  updatedAt?: string;
  createdFrom?: {
    id: number;
    fullName: string;
  };
  updatedFrom?: {
    id: number;
    fullName: string;
  } | null;
  permissions?: {
    canView: boolean;
    canCreate: boolean;
    canEdit: boolean;
    canDelete: boolean;
  };
}
