import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface NationalityDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  nationalityName?: string;
}

export const NationalityDeleteConfirmationModal: React.FC<NationalityDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  nationalityName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Nationalität löschen?"
    message={`Soll die Nationalität${nationalityName ? ` "${nationalityName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default NationalityDeleteConfirmationModal;
