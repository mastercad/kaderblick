export interface AppNotification {
  id: string;
  type: 'news' | 'message' | 'participation' | 'system';
  title: string;
  message: string;
  timestamp: Date;
  read: boolean;
  data?: any;
  showToast?: boolean;
  showPush?: boolean;
}

export interface NotificationContextType {
  notifications: AppNotification[];
  addNotification: (notification: Omit<AppNotification, 'id' | 'timestamp' | 'read'>) => void;
  markAsRead: (id: string) => void;
  markAllAsRead: () => void;
  removeNotification: (id: string) => void;
  clearAll: () => void;
  unreadCount: number;
  requestPermission: () => Promise<boolean>;
}

export const NOTIFICATION_TYPES = {
  NEWS: 'news' as const,
  MESSAGE: 'message' as const,
  PARTICIPATION: 'participation' as const,
  SYSTEM: 'system' as const,
} as const;
