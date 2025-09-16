import { useState } from 'react';
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
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await login({ email, password });
      if (onSuccess) onSuccess();
    } catch (err) {
      setError('Login fehlgeschlagen');
    }
  };

  return (
    <Box component="form" 
        sx={{ p: 2, display: 'flex', flexDirection: 'column', gap: 1 }}
        onSubmit={handleSubmit}
    >
        <GoogleLoginButton/>
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
        <Button type="submit" variant="contained" size="small" sx={{ mt: 2 }}>
        Login
    </Button>
</Box>
  );
}
