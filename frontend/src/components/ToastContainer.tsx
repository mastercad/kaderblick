import React from 'react';
import { ToastNotification } from './ToastNotification';
import { AppNotification } from '../types/notifications';

interface ToastContainerProps {
  toasts: AppNotification[];
  onRemoveToast: (id: string) => void;
}

export const ToastContainer: React.FC<ToastContainerProps> = ({
  toasts,
  onRemoveToast
}) => {
  return (
    <div style={{ position: 'fixed', top: 0, right: 0, zIndex: 9999 }}>
      {toasts.map((toast, index) => (
        <div
          key={`toast-${toast.id}-${index}`}
          style={{
            marginTop: index * 70, // Versetzt die Toasts
          }}
        >
          <ToastNotification
            notification={toast}
            onClose={() => onRemoveToast(toast.id)}
          />
        </div>
      ))}
    </div>
  );
};
