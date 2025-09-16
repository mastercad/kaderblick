import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface PositionDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  positionName?: string;
}

export const PositionDeleteConfirmationModal: React.FC<PositionDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  positionName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Position löschen?"
    message={`Soll die Position${positionName ? ` "${positionName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default PositionDeleteConfirmationModal;
