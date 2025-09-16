import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface CoachLicenseDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  coachLicenseName?: string;
}

export const CoachLicenseDeleteConfirmationModal: React.FC<CoachLicenseDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  coachLicenseName,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Trainerlizenz löschen?"
    message={`Soll die Trainerlizenz${coachLicenseName ? ` "${coachLicenseName}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);

export default CoachLicenseDeleteConfirmationModal;
