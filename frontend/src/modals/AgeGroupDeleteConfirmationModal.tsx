import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface AgeGroupDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  ageGroupName?: string;
}

export const AgeGroupDeleteConfirmationModal: React.FC<AgeGroupDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  ageGroupName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Altersgruppe löschen?"
    message={`Soll die Altersgruppe${ageGroupName ? ` "${ageGroupName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default AgeGroupDeleteConfirmationModal;
