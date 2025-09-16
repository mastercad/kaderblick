import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface StrongFeetDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  positionName?: string;
}

export const StrongFeetDeleteConfirmationModal: React.FC<StrongFeetDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  positionName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Starken Fuß löschen?"
    message={`Soll der Starke Fuß${positionName ? ` "${positionName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default StrongFeetDeleteConfirmationModal;
