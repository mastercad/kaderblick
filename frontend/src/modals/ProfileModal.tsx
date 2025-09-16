import React from 'react';
import LinkIcon from '@mui/icons-material/Link';
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Divider from '@mui/material/Divider';
import MenuItem from '@mui/material/MenuItem';
import Alert from '@mui/material/Alert';
import { apiJson } from '../utils/api';

interface ProfileData {
  firstName: string;
  lastName: string;
  email: string;
  height?: number | '';
  weight?: number | '';
  shoeSize?: number | '';
  shirtSize?: string;
  pantsSize?: string;
  password?: string;
  confirmPassword?: string;
}

// Typ für UserRelation
interface UserRelation {
  id: number;
  fullName: string;
  category: string;
  identifier: string;
}

export interface ProfileModalProps {
  open: boolean;
  onClose: () => void;
  onSave?: (data: ProfileData) => void;
}

const ProfileModal: React.FC<ProfileModalProps> = ({ open, onClose, onSave }) => {
  const [form, setForm] = React.useState<ProfileData>({
    firstName: '',
    lastName: '',
    email: '',
    height: '',
    weight: '',
    shoeSize: '',
    shirtSize: '',
    pantsSize: '',
    password: '',
    confirmPassword: ''
  });
  const [loading, setLoading] = React.useState(false);
  const [message, setMessage] = React.useState<{ text: string; type: 'success' | 'error' } | null>(null);
  const [relations, setRelations] = React.useState<UserRelation[]>([]);
  const [relationsOpen, setRelationsOpen] = React.useState(false);

  // Profildaten laden beim Öffnen des Modals
  React.useEffect(() => {
    if (open) {
      loadUserProfile();
      loadUserRelations();
    }
  }, [open]);

  // Lädt die UserRelation-Verknüpfungen
  const loadUserRelations = async () => {
    try {
      const data = await apiJson('/api/users/relations');
      setRelations(Array.isArray(data) ? data : []);
    } catch (e) {
      setRelations([]);
    }
  };

  const loadUserProfile = async () => {
    try {
      const userData = await apiJson('/api/about-me');
      setForm({
        firstName: userData.firstName || '',
        lastName: userData.lastName || '',
        email: userData.email || '',
        height: userData.height || '',
        weight: userData.weight || '',
        shoeSize: userData.shoeSize || '',
        shirtSize: userData.shirtSize || '',
        pantsSize: userData.pantsSize || '',
        password: '',
        confirmPassword: ''
      });
    } catch (error) {
      console.error('Fehler beim Laden des Profils:', error);
      setMessage({ text: 'Fehler beim Laden des Profils', type: 'error' });
    }
  };

  const handleSave = async () => {
    if (form.password && form.password !== form.confirmPassword) {
      setMessage({ text: 'Die Passwörter stimmen nicht überein!', type: 'error' });
      return;
    }

    setLoading(true);
    setMessage(null);

    try {
      const updateData = {
        firstName: form.firstName,
        lastName: form.lastName,
        email: form.email,
        height: form.height,
        weight: form.weight,
        shoeSize: form.shoeSize,
        shirtSize: form.shirtSize,
        pantsSize: form.pantsSize,
        ...(form.password ? { password: form.password } : {})
      };

      const response = await apiJson('/api/update-profile', {
        method: 'PUT',
        body: updateData
      });

      setMessage({ text: 'Profil erfolgreich aktualisiert!', type: 'success' });
      
      if (response.emailVerificationRequired) {
        setMessage({ 
          text: 'Profil erfolgreich aktualisiert! Bitte bestätigen Sie Ihre neue E-Mail-Adresse.', 
          type: 'success' 
        });
      }

      // Passwort-Felder zurücksetzen
      setForm(prev => ({ ...prev, password: '', confirmPassword: '' }));
      
      if (onSave) {
        onSave(form);
      }
    } catch (error: any) {
      console.error('Fehler beim Aktualisieren des Profils:', error);
      setMessage({ text: error.message || 'Fehler beim Aktualisieren des Profils', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const shirtSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
  const pantsSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '28/30', '30/30', '32/30', '34/30', '36/30', '28/32', '30/32', '32/32', '34/32', '36/32'];

  return (
    <>
      <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          Profil bearbeiten
          <Tooltip title={relations.length > 0 ? 'Mit Profil(en) verknüpft' : 'Mit keinem Profil verknüpft'}>
            <span>
              <IconButton
                size="small"
                onClick={() => relations.length > 0 && setRelationsOpen(true)}
                sx={{ color: relations.length > 0 ? 'success.main' : 'grey.400', ml: 1 }}
                disabled={relations.length === 0}
              >
                <LinkIcon />
              </IconButton>
            </span>
          </Tooltip>
        </DialogTitle>
      <DialogContent>
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2, mt: 1 }}>
          <Box sx={{ display: 'flex', gap: 2, flexDirection: { xs: 'column', md: 'row' } }}>
            <TextField
              label="Vorname"
              value={form.firstName}
              onChange={e => setForm(prev => ({ ...prev, firstName: e.target.value }))}
              fullWidth
              required
            />
            <TextField
              label="Nachname"
              value={form.lastName}
              onChange={e => setForm(prev => ({ ...prev, lastName: e.target.value }))}
              fullWidth
              required
            />
          </Box>
          
          <TextField
            label="E-Mail"
            type="email"
            value={form.email}
            onChange={e => setForm(prev => ({ ...prev, email: e.target.value }))}
            fullWidth
            required
          />
          
          <Box sx={{ display: 'flex', gap: 2, flexDirection: { xs: 'column', md: 'row' } }}>
            <TextField
              label="Körpergröße (cm)"
              type="number"
              value={form.height}
              onChange={e => setForm(prev => ({ ...prev, height: e.target.value ? Number(e.target.value) : '' }))}
              fullWidth
            />
            <TextField
              label="Gewicht (kg)"
              type="number"
              value={form.weight}
              onChange={e => setForm(prev => ({ ...prev, weight: e.target.value ? Number(e.target.value) : '' }))}
              fullWidth
            />
            <TextField
              label="Schuhgröße (EU)"
              type="number"
              inputProps={{ step: 0.5 }}
              value={form.shoeSize}
              onChange={e => setForm(prev => ({ ...prev, shoeSize: e.target.value ? Number(e.target.value) : '' }))}
              fullWidth
            />
          </Box>
          
          <Box sx={{ display: 'flex', gap: 2, flexDirection: { xs: 'column', md: 'row' } }}>
            <TextField
              label="T-Shirt Größe"
              select
              value={form.shirtSize}
              onChange={e => setForm(prev => ({ ...prev, shirtSize: e.target.value }))}
              fullWidth
            >
              <MenuItem value="">Bitte wählen</MenuItem>
              {shirtSizes.map(size => (
                <MenuItem key={size} value={size}>{size}</MenuItem>
              ))}
            </TextField>
            <TextField
              label="Hosengröße"
              select
              value={form.pantsSize}
              onChange={e => setForm(prev => ({ ...prev, pantsSize: e.target.value }))}
              fullWidth
            >
              <MenuItem value="">Bitte wählen</MenuItem>
              {pantsSizes.map(size => (
                <MenuItem key={size} value={size}>{size}</MenuItem>
              ))}
            </TextField>
          </Box>
          
          <Divider sx={{ my: 2 }} />
          <Typography variant="h6" gutterBottom>Passwort ändern</Typography>
          
          <Box sx={{ display: 'flex', gap: 2, flexDirection: { xs: 'column', md: 'row' } }}>
            <TextField
              label="Neues Passwort"
              type="password"
              value={form.password}
              onChange={e => setForm(prev => ({ ...prev, password: e.target.value }))}
              fullWidth
            />
            <TextField
              label="Passwort bestätigen"
              type="password"
              value={form.confirmPassword}
              onChange={e => setForm(prev => ({ ...prev, confirmPassword: e.target.value }))}
              fullWidth
            />
          </Box>
          
          {message && (
            <Alert severity={message.type} sx={{ mt: 2 }}>
              {message.text}
            </Alert>
          )}
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Abbrechen</Button>
        <Button 
          onClick={handleSave} 
          color="primary" 
          variant="contained"
          disabled={loading}
        >
          {loading ? 'Speichere...' : 'Speichern'}
        </Button>
      </DialogActions>
      </Dialog>
      {/* Modal für UserRelation-Übersicht */}
      <Dialog open={relationsOpen} onClose={() => setRelationsOpen(false)} maxWidth="sm" fullWidth>
        <DialogTitle>Verknüpfte Profile</DialogTitle>
        <DialogContent>
          {relations.length === 0 ? (
            <Typography color="text.secondary">Keine Verknüpfungen vorhanden.</Typography>
          ) : (
            <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
              {relations.map(rel => (
                <Box key={rel.id} sx={{ p: 1, border: '1px solid #eee', borderRadius: 1 }}>
                  <Typography variant="subtitle1">{rel.fullName}</Typography>
                  <Typography variant="body2" color="text.secondary">
                    Typ: {rel.category} - {rel.identifier}
                  </Typography>
                </Box>
              ))}
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setRelationsOpen(false)}>Schließen</Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default ProfileModal;
