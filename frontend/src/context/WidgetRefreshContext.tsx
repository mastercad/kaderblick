import React, { createContext, useContext, useState, useCallback } from 'react';

interface WidgetRefreshContextType {
  refreshWidget: (widgetId: string) => void;
  isRefreshing: (widgetId: string) => boolean;
  getRefreshTrigger: (widgetId: string) => number;
}

const WidgetRefreshContext = createContext<WidgetRefreshContextType | undefined>(undefined);

export const useWidgetRefresh = () => {
  const context = useContext(WidgetRefreshContext);
  if (!context) {
    throw new Error('useWidgetRefresh must be used within WidgetRefreshProvider');
  }
  return context;
};

interface WidgetRefreshProviderProps {
  children: React.ReactNode;
}

export const WidgetRefreshProvider: React.FC<WidgetRefreshProviderProps> = ({ children }) => {
  const [refreshingWidgets, setRefreshingWidgets] = useState<Set<string>>(new Set());
  const [refreshTriggers, setRefreshTriggers] = useState<Map<string, number>>(new Map());

  const refreshWidget = useCallback((widgetId: string) => {
    setRefreshingWidgets(prev => new Set([...prev, widgetId]));
    
    // Increment refresh trigger for this widget
    setRefreshTriggers(prev => {
      const newMap = new Map(prev);
      newMap.set(widgetId, (newMap.get(widgetId) || 0) + 1);
      return newMap;
    });

    // Remove from refreshing set after a short delay (for UI feedback)
    setTimeout(() => {
      setRefreshingWidgets(prev => {
        const newSet = new Set(prev);
        newSet.delete(widgetId);
        return newSet;
      });
    }, 500);
  }, []);

  const isRefreshing = useCallback((widgetId: string) => {
    return refreshingWidgets.has(widgetId);
  }, [refreshingWidgets]);

  const getRefreshTrigger = useCallback((widgetId: string) => {
    return refreshTriggers.get(widgetId) || 0;
  }, [refreshTriggers]);

  return (
    <WidgetRefreshContext.Provider value={{
      refreshWidget,
      isRefreshing,
      getRefreshTrigger
    }}>
      {children}
    </WidgetRefreshContext.Provider>
  );
};
