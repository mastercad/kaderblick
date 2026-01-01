export interface VideoType {
  id: number;
  name: string;
  sort: number;
  createdAt?: string;
  updatedAt?: string;
  createdFrom?: {
    id: number;
    fullName: string;
  };
  updatedFrom?: {
    id: number;
    fullName: string;
  };
  permissions?: {
    canView: boolean;
    canCreate: boolean;
    canEdit: boolean;
    canDelete: boolean;
  };
}
