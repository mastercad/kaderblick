import Dialog from '@mui/material/Dialog';
import Tabs from '@mui/material/Tabs';
import Tab from '@mui/material/Tab';
import { useState, useEffect } from 'react';
import LoginForm from './LoginForm';
import RegisterForm from './RegisterForm';
import Alert from '@mui/material/Alert';
import { useAuth } from '../context/AuthContext';

interface AuthModalProps {
  open: boolean;
  onClose: () => void;
}

interface GoogleAuthMessage {
  source: string;
  success?: boolean;
  token?: string;
  refreshToken?: string;
  user?: {
    id: number;
    email: string;
    name: string;
    firstName: string;
    lastName: string;
  };
  error?: string;
  message?: string;
}

export default function AuthModal({ open, onClose }: AuthModalProps) {
  const { loginWithGoogle } = useAuth();
  const [tab, setTab] = useState<'login' | 'register'>('login');
  const [googleLoginError, setGoogleLoginError] = useState<string | null>(null);
  const [googleLoginSuccess, setGoogleLoginSuccess] = useState<string | null>(null);

  useEffect(() => {
//    console.log('🔧 AuthModal: Setting up message listener...');
    
    const handler = (event: MessageEvent<GoogleAuthMessage>) => {
//      console.log("📥 AuthModal received message:", event.data);
//      console.log("📥 Message origin:", event.origin);
//      console.log("📥 Message source:", event.source);
      
      // Nur Nachrichten von unserer Google-Auth verarbeiten
      if (
        typeof event.data === 'object' &&
        event.data !== null &&
        event.data.source === 'google-auth'
      ) {
//        console.log("✅ Processing google-auth message...");
        try {
          if (event.data.success && event.data.token && event.data.user) {
            // Erfolgreiche Authentifizierung
//            console.log("✅ Login successful, calling loginWithGoogle...");
            loginWithGoogle(event.data);
            setGoogleLoginSuccess(`Willkommen, ${event.data.user.name}!`);
            setGoogleLoginError(null);
            
            // Modal nach kurzer Verzögerung schließen
            setTimeout(() => {
//              console.log("✅ Closing modal...");
              onClose();
              setGoogleLoginSuccess(null);
            }, 1500);
            
          } else {
            // Fehler bei der Authentifizierung
            const errorMessage = event.data.message || event.data.error || 'Unbekannter Fehler';
//            console.log("❌ Auth error:", errorMessage);
            setGoogleLoginError(`Google-Login fehlgeschlagen: ${errorMessage}`);
            setGoogleLoginSuccess(null);
          }
        } catch (error) {
//          console.error('❌ Error processing Google auth:', error);
          setGoogleLoginError('Fehler bei der Verarbeitung der Google-Authentifizierung');
          setGoogleLoginSuccess(null);
        }
      } else {
//        console.log("❌ Ignoring message (wrong source):", event.data.source);
      }
    };

    window.addEventListener('message', handler);
//    console.log('✅ Message listener added');
    
    return () => {
//      console.log('🗑️ Removing message listener');
      window.removeEventListener('message', handler);
    };
  }, [loginWithGoogle, onClose]);

  // Error und Success nach einer Zeit zurücksetzen
  useEffect(() => {
    if (googleLoginError || googleLoginSuccess) {
      const timer = setTimeout(() => {
        setGoogleLoginError(null);
        setGoogleLoginSuccess(null);
      }, 5000);
      return () => clearTimeout(timer);
    }
  }, [googleLoginError, googleLoginSuccess]);

  if (!open) return null;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
        <Tabs value={tab} onChange={(_, v) => setTab(v)} variant="fullWidth">
            <Tab label="Login" value="login" />
            <Tab label="Register" value="register" />
        </Tabs>
        
        {googleLoginError && (
          <Alert severity="error" sx={{ m: 2 }}>
            {googleLoginError}
          </Alert>
        )}
        
        {googleLoginSuccess && (
          <Alert severity="success" sx={{ m: 2 }}>
            {googleLoginSuccess}
          </Alert>
        )}
        
        {tab === 'login' ? <LoginForm onSuccess={onClose} /> : <RegisterForm />}
    </Dialog>
  );
}
