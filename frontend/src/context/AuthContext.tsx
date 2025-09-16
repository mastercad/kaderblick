import { createContext, useContext, useState, ReactNode, useEffect } from 'react';
import { apiJson, apiRequest } from '../utils/api';

interface User {
  id: number;
  email: string;
  name: string;
  firstName: string;
  lastName: string;
  height?: number;
  weight?: number;
  shoeSize?: number;
  shirtSize?: string;
  pantsSize?: string;
  isCoach?: boolean;
  isPlayer?: boolean;
  roles: { [key: string]: string };
}

interface AuthData {
  success?: boolean;
  token?: string;
  refreshToken?: string;
  user?: User;
  error?: string;
  message?: string;
}

type AuthContextType = {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  isSuperAdmin: boolean;
  login: (credentials: { email: string; password: string }) => Promise<void>;
  loginWithGoogle: (data: AuthData) => void;
  logout: () => void;
  checkAuthStatus: () => Promise<void>;
};

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const isAuthenticated = Boolean(user);
  const isSuperAdmin = Boolean(user?.roles && Object.values(user.roles).includes('ROLE_SUPERADMIN'));

  const checkAuthStatus = async () => {
    setIsLoading(true);
    try {
      const userData = await apiJson<User>('/api/about-me');
      setUser(userData);
//      console.log('Auth check successful:', userData);
    } catch (error) {
//      console.error('Auth check failed:', error);
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  };

  const login = async (credentials: { email: string; password: string }) => {
    try {
      await apiJson('/api/login', {
        method: 'POST',
        body: credentials
      });
      
      // Nach erfolgreichem Login User-Daten laden
      await checkAuthStatus();
    } catch (error) {
      throw error; // Fehler weiterwerfen für UI-Handling
    }
  };

  const loginWithGoogle = (authData: AuthData) => {
//    console.log('Processing Google auth data:', authData);
    
    if (authData.success && authData.user) {
      // Token werden als HttpOnly Cookies gesetzt
      // Wir setzen nur die User-Daten im State
      setUser(authData.user);
//      console.log('Google login successful:', authData.user);
    } else {
//      console.error('Google login failed:', authData.error || authData.message);
      throw new Error(authData.message || authData.error || 'Google-Login fehlgeschlagen');
    }
  };

  const logout = async () => {
    try {
      await apiRequest('/api/logout', { method: 'POST' });
    } catch (error) {
//      console.error('Logout request failed:', error);
    } finally {
      // Immer User-State zurücksetzen
      setUser(null);
    }
  };

  // Auth-Status beim App-Start prüfen
  useEffect(() => {
    checkAuthStatus();
  }, []);

  return (
    <AuthContext.Provider value={{ 
      user, 
      isAuthenticated, 
      isLoading,
      isSuperAdmin,
      login, 
      logout, 
      loginWithGoogle,
      checkAuthStatus
    }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within an AuthProvider');
  return ctx;
}
