import React, { createContext, useContext, useState, ReactNode } from 'react';

interface HomeScrollContextType {
  isOnHeroSection: boolean;
  setIsOnHeroSection: (isOnHero: boolean) => void;
}

const HomeScrollContext = createContext<HomeScrollContextType | undefined>(undefined);

export function HomeScrollProvider({ children }: { children: ReactNode }) {
  const [isOnHeroSection, setIsOnHeroSection] = useState(true);

  return (
    <HomeScrollContext.Provider value={{ isOnHeroSection, setIsOnHeroSection }}>
      {children}
    </HomeScrollContext.Provider>
  );
}

export function useHomeScroll() {
  const context = useContext(HomeScrollContext);
  if (context === undefined) {
    // Return default values if not within provider (e.g., on other pages)
    return { isOnHeroSection: true, setIsOnHeroSection: () => {} };
  }
  return context;
}
