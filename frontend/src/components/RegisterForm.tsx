import { useState } from 'react';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import Alert from '@mui/material/Alert';
import { apiJson } from '../utils/api';


export default function RegisterForm() {
  const [fullName, setFullName] = useState('');
  const [password, setPassword] = useState('');
  const [email, setEmail] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    
    try {
      const response = await apiJson('/api/register', {
        method: 'POST',
        body: { fullName, email, password }
      });
      
      if (response && 'error' in response) {
        setError(response.error);
      } else {
        setSuccess(true);
      }
    } catch (err) {
      setError('Registrierung fehlgeschlagen. Bitte versuche es später erneut.');
    }
  };

  if (success) return <div>Registrierung erfolgreich! Du kannst dich jetzt einloggen.</div>;

  return (
    <Box component="form" 
        sx={{ p: 2, display: 'flex', flexDirection: 'column', gap: 1 }}
        onSubmit={handleSubmit}
    >
        {error && <Alert severity="error">{error}</Alert>}
        
        <TextField
            label="Vollständiger Name"
            variant="standard"
            size="small"
            fullWidth
            value={fullName}
            onChange={e => setFullName(e.target.value)}
            required
        />
        <TextField
            label="E-Mail"
            type="email"
            variant="standard"
            size="small"
            fullWidth
            value={email}
            onChange={e => setEmail(e.target.value)}
            required
        />
        <TextField
            label="Passwort"
            type="password"
            variant="standard"
            size="small"
            fullWidth
            value={password}
            onChange={e => setPassword(e.target.value)}
            required
        />
        <Button type="submit" variant="contained" size="small" sx={{ mt: 2 }}>
            Registrieren
        </Button>
    </Box>
  );
}
