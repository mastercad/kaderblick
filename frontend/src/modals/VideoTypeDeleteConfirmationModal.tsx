import React from 'react';
import { ConfirmationModal } from './ConfirmationModal';

interface VideoTypeDeleteConfirmationModalProps {
  open: boolean;
  videoTypeName?: string;
  onClose: () => void;
  onConfirm: () => void;
}

const VideoTypeDeleteConfirmationModal: React.FC<VideoTypeDeleteConfirmationModalProps> = ({
  open,
  videoTypeName,
  onClose,
  onConfirm,
}) => {
  return (
    <ConfirmationModal
      open={open}
      onClose={onClose}
      onConfirm={onConfirm}
      title="Videotyp löschen"
      message={`Möchten Sie den Videotyp "${videoTypeName}" wirklich löschen?`}
      confirmText="Löschen"
      confirmColor="error"
    />
  );
};

export default VideoTypeDeleteConfirmationModal;
