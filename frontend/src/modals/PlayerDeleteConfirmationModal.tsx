import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface PlayerDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  playerName?: string;
}

export const PlayerDeleteConfirmationModal: React.FC<PlayerDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  playerName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Spieler löschen?"
    message={`Soll der Spieler${playerName ? ` "${playerName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default PlayerDeleteConfirmationModal;
