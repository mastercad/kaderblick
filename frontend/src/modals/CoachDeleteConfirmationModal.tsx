import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface CoachDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  coachName?: string;
}

export const CoachDeleteConfirmationModal: React.FC<CoachDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  coachName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Trainer löschen?"
    message={`Soll der Trainer${coachName ? ` "${coachName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default CoachDeleteConfirmationModal;
