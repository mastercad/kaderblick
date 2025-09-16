import React from 'react';
import FabStackProvider from './FabStackProvider';
import FabStack from './FabStack';

// This component wraps the app with the FabStackProvider and renders the FabStack globally
const FabStackRoot: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <FabStackProvider>
    {children}
    <FabStack />
  </FabStackProvider>
);

export default FabStackRoot;
