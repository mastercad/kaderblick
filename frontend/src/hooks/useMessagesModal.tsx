import React, { useState } from 'react';
import { MessagesModal } from '../modals/MessagesModal';

export const useMessagesModal = () => {
  const [isOpen, setIsOpen] = useState(false);

  const openMessages = () => setIsOpen(true);
  const closeMessages = () => setIsOpen(false);

  const MessagesModalComponent = () => (
    <MessagesModal open={isOpen} onClose={closeMessages} />
  );

  return {
    openMessages,
    closeMessages,
    MessagesModal: MessagesModalComponent,
    isOpen
  };
};
