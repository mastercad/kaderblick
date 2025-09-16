import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface SurfaceTypeDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  surfaceTypeName?: string;
}

export const SurfaceTypeDeleteConfirmationModal: React.FC<SurfaceTypeDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  surfaceTypeName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Spielfeldoberfläche löschen?"
    message={`Soll die Spielfeldoberfläche${surfaceTypeName ? ` "${surfaceTypeName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default SurfaceTypeDeleteConfirmationModal;
