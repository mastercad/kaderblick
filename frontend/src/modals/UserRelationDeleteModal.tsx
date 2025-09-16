import React from 'react';
import { DynamicConfirmationModal } from './DynamicConfirmationModal';

// Modal zum Löschen einer User-Relation, erbt von DynamicConfirmationModal
const UserRelationDeleteModal: React.FC<{ open: boolean; onClose: () => void; onConfirm: () => void; userRelation?: any; }> = ({ open, onClose, onConfirm, userRelation }) => {
  return (
    <DynamicConfirmationModal
      open={open}
      onClose={onClose}
      onConfirm={onConfirm}
      title="Zuordnung löschen"
      message="Möchten Sie diese Zuordnung wirklich löschen?"
      confirmText="Löschen"
      cancelText="Abbrechen"
      confirmColor="error"
    />
  );
};

export default UserRelationDeleteModal;
