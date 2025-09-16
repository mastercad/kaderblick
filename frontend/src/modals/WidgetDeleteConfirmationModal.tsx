import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface WidgetDeleteConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  widgetTitle?: string;
}

export const WidgetDeleteConfirmationModal: React.FC<WidgetDeleteConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  widgetTitle,
}) => (
  <ConfirmationModal
    open={open}
    onClose={onClose}
    onConfirm={onConfirm}
    title="Widget löschen?"
    message={`Soll das Widget${widgetTitle ? ` "${widgetTitle}"` : ''} wirklich entfernt werden?`}
    confirmText="Löschen"
    confirmColor="error"
  />
);
