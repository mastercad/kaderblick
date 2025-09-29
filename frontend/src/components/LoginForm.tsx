import { useState, useEffect } from 'react';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import Divider from '@mui/material/Divider';
import { useAuth } from '../context/AuthContext';
import GoogleLoginButton from './GoogleLoginButton';

interface LoginFormProps {
  onSuccess?: () => void;
}

export default function LoginForm({ onSuccess }: LoginFormProps) {
  const { login, user } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    if (user && onSuccess) {
      onSuccess();
    }
  }, [user, onSuccess]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    try {
      await login({ email, password });
      // Nach Login: user ist im Context aktualisiert
      if (!user) {
        setError('Ungültige Zugangsdaten');
        return;
      }
      if (onSuccess) onSuccess();
    } catch (err: any) {
      if (err && typeof err === 'object' && 'error' in err && err.error === 'Invalid credentials') {
        setError('Ungültige Zugangsdaten');
      } else {
        setError('Login fehlgeschlagen');
      }
    }
  };

  return (
    <Box component="form"
      sx={{ p: 2, display: 'flex', flexDirection: 'column', gap: 1 }}
      onSubmit={handleSubmit}
    >
      <GoogleLoginButton />
      oder
      <Divider />
      <TextField
        label="E-Mail"
        variant="standard"
        size="small"
        fullWidth
        value={email}
        onChange={(e) => setEmail(e.target.value)}
      />
      <TextField
        label="Passwort"
        type="password"
        variant="standard"
        size="small"
        fullWidth
        value={password}
        onChange={(e) => setPassword(e.target.value)}
      />
      {error && (
        <Box sx={{ color: 'error.main', mt: 1, mb: 1 }}>{error}</Box>
      )}
      <Button type="submit" variant="contained" size="small" sx={{ mt: 2 }}>
        Login
      </Button>
    </Box>
  );
}
