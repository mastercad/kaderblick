import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface LeagueDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  positionName?: string;
}

export const LeagueDeleteConfirmationModal: React.FC<LeagueDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  positionName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Liga löschen?"
    message={`Soll die Liga${positionName ? ` "${positionName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default LeagueDeleteConfirmationModal;
