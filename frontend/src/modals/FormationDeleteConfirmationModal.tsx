import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface FormationDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  formationName?: string;
}

export const FormationDeleteConfirmationModal: React.FC<FormationDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  formationName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Formation löschen?"
    message={`Soll die Formation${formationName ? ` "${formationName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default FormationDeleteConfirmationModal;
