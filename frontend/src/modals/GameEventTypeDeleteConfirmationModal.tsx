import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface GameEventTypeDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  gameEventTypeName?: string;
}

export const GameEventTypeDeleteConfirmationModal: React.FC<GameEventTypeDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  gameEventTypeName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Spielereignistyp löschen?"
    message={`Soll das Spielereignis${gameEventTypeName ? ` "${gameEventTypeName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default GameEventTypeDeleteConfirmationModal;
