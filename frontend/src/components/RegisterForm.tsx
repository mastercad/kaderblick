import { useState } from 'react';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';


export default function RegisterForm() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [email, setEmail] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      // Hier API-Call zum Registrieren
      // await register({ username, email, password });
      setSuccess(true);
    } catch (err) {
      setError('Registrierung fehlgeschlagen');
    }
  };

  if (success) return <div>Registrierung erfolgreich! Du kannst dich jetzt einloggen.</div>;

  return (
    <Box component="form" 
        sx={{ p: 2, display: 'flex', flexDirection: 'column', gap: 1 }}
        onSubmit={handleSubmit}
    >
        <TextField
            label="Benutzername"
            variant="standard"
            size="small"
            fullWidth
            value={username}
            onChange={e => setUsername(e.target.value)}
        />
        <TextField
            label="E-Mail"
            variant="standard"
            size="small"
            fullWidth
            value={email}
            onChange={e => setEmail(e.target.value)}
        />
        <TextField
            label="Passwort"
            type="password"
            variant="standard"
            size="small"
            fullWidth
            value={password}
            onChange={e => setPassword(e.target.value)}
        />
        <Button type="submit" variant="contained" size="small" sx={{ mt: 2 }}>
            Registrieren
        </Button>
    </Box>
  );
}
