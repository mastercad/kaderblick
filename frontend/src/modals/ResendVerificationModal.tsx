import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface ResendVerificationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  userName?: string;
}

const ResendVerificationModal: React.FC<ResendVerificationModalProps> = ({
  open,
  onClose,
  onConfirm,
  userName = 'Benutzer',
}) => {
  return (
    <ConfirmationModal
      open={open}
      onClose={onClose}
      onConfirm={onConfirm}
      title="Verifizierungslink erneut senden"
      message={`Möchten Sie dem Benutzer "${userName}" einen neuen Verifizierungslink per E-Mail zusenden? Die aktuelle Verifizierung wird aufgehoben und der Benutzer muss die E-Mail-Adresse erneut bestätigen.`}
      confirmText="Link senden"
      cancelText="Abbrechen"
      confirmColor="warning"
    />
  );
};

export default ResendVerificationModal;
