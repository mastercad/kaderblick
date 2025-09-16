import React, { createContext, useContext, useState } from 'react';

type FabStackContextType = {
  addFab: (fab: React.ReactNode, key: string) => void;
  removeFab: (key: string) => void;
  fabs: { key: string; node: React.ReactNode }[];
};

const FabStackContext = createContext<FabStackContextType | undefined>(undefined);

export const useFabStack = () => useContext(FabStackContext);

const FabStackProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [fabs, setFabs] = useState<{ key: string; node: React.ReactNode }[]>([]);

  const addFab = (fab: React.ReactNode, key: string) => {
    setFabs(prev => {
      if (prev.some(f => f.key === key)) return prev;
      return [...prev, { key, node: fab }];
    });
  };
  const removeFab = (key: string) => {
    setFabs(prev => prev.filter(f => f.key !== key));
  };

  return (
    <FabStackContext.Provider value={{ addFab, removeFab, fabs }}>
      {children}
    </FabStackContext.Provider>
  );
};

export default FabStackProvider;