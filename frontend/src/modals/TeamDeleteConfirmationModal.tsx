import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface TeamDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  teamName?: string;
}

export const TeamDeleteConfirmationModal: React.FC<TeamDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  teamName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Team löschen?"
    message={`Soll der Team${teamName ? ` "${teamName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default TeamDeleteConfirmationModal;
