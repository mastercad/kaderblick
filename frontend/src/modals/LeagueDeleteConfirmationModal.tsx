import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface LeagueDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  leagueName?: string;
}

export const LeagueDeleteConfirmationModal: React.FC<LeagueDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  leagueName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Liga löschen?"
    message={`Soll die Liga${leagueName ? ` "${leagueName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default LeagueDeleteConfirmationModal;
