import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface CameraDeleteConfirmationModalProps {
  open: boolean;
  cameraName?: string;
  onClose: () => void;
  onConfirm: () => void;
}

const CameraDeleteConfirmationModal: React.FC<CameraDeleteConfirmationModalProps> = ({
  open,
  cameraName,
  onClose,
  onConfirm,
}) => {
  return (
    <ConfirmationModal
      open={open}
      onClose={onClose}
      onConfirm={onConfirm}
      title="Kamera löschen"
      message={`Möchten Sie die Kamera "${cameraName}" wirklich löschen?`}
      confirmText="Löschen"
      confirmColor="error"
    />
  );
};

export default CameraDeleteConfirmationModal;
