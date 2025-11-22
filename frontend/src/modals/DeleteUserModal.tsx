import React from 'react';
import { DynamicConfirmationModal } from './DynamicConfirmationModal';

interface DeleteUserModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  user?: {
    fullName?: string;
    email?: string;
  };
}

// Modal zum Löschen eines Benutzers, erbt von DynamicConfirmationModal
const DeleteUserModal: React.FC<DeleteUserModalProps> = ({ open, onClose, onConfirm, user }) => {
  const message = user 
    ? `Möchten Sie den Benutzer "${user.fullName || user.email}" wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.`
    : 'Möchten Sie diesen Benutzer wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.';

  return (
    <DynamicConfirmationModal
      open={open}
      onClose={onClose}
      onConfirm={onConfirm}
      title="Benutzer löschen"
      message={message}
      confirmText="Löschen"
      cancelText="Abbrechen"
      confirmColor="error"
    />
  );
};

export default DeleteUserModal;
