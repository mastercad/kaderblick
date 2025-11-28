import React from 'react';
import InfoOutlinedIcon from '@mui/icons-material/InfoOutlined';
import CircularProgress from '@mui/material/CircularProgress';

// XP Breakdown Modal (lädt echte Daten inkl. Titel/Level)
const XpBreakdownModal: React.FC<{ open: boolean; onClose: () => void }> = ({ open, onClose }) => {
  const [loading, setLoading] = React.useState(false);
  const [breakdown, setBreakdown] = React.useState<Array<{ actionType: string; label: string; xp: number }>>([]);
  const [error, setError] = React.useState<string | null>(null);
  const [title, setTitle] = React.useState<any>(null);
  const [allTitles, setAllTitles] = React.useState<any[]>([]);
  const [level, setLevel] = React.useState<any>(null);
  const [xpTotal, setXpTotal] = React.useState<number|null>(null);

  React.useEffect(() => {
    if (open) {
      setLoading(true);
      setError(null);
      setBreakdown([]);
      setTitle(null);
      setAllTitles([]);
      setLevel(null);
      setXpTotal(null);
      import('../utils/api').then(({ apiJson }) => {
        apiJson('/api/xp-breakdown')
          .then((data: any) => {
            if (data && Array.isArray(data.breakdown)) {
              setBreakdown(data.breakdown);
              setTitle(data.title || null);
              setAllTitles(Array.isArray(data.allTitles) ? data.allTitles : []);
              setLevel(data.level || null);
              setXpTotal(typeof data.xpTotal === 'number' ? data.xpTotal : null);
            } else if (data && data.error) {
              setError(data.error);
            } else {
              setError('Unbekannter Fehler beim Laden der XP-Daten');
            }
          })
          .catch(() => setError('Fehler beim Laden der XP-Daten'))
          .finally(() => setLoading(false));
      });
    }
  }, [open]);

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      title="Erfahrungspunkte – Aufschlüsselung"
      maxWidth="sm"
      actions={<Button onClick={onClose} variant="contained">Schließen</Button>}
    >
      <Box sx={{ p: 2 }}>
        <Typography variant="body1" gutterBottom>
          Hier siehst du, wie sich deine Erfahrungspunkte (XP) auf der Plattform zusammensetzen.
        </Typography>
        <Box sx={{ mt: 2 }}>
          {loading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', my: 4 }}><CircularProgress /></Box>
          ) : error ? (
            <Alert severity="error">{error}</Alert>
          ) : (
            <>
              {title && (
                <Typography variant="subtitle1" sx={{ mb: 1 }}>
                  <b>Haupttitel:</b> {title.displayName}
                </Typography>
              )}
              {allTitles.length > 1 && (
                <Box sx={{ mb: 1 }}>
                  <Typography variant="subtitle2">Weitere Titel:</Typography>
                  <ul style={{ margin: 0 }}>
                    {allTitles.filter(t => !title || t.id !== title.id).map(t => (
                      <li key={t.id}>{t.displayName}</li>
                    ))}
                  </ul>
                </Box>
              )}
              {level && (
                <Typography variant="subtitle2" sx={{ mb: 1 }}>
                  <b>Level:</b> {level.level} &nbsp; <b>XP:</b> {xpTotal ?? level.xpTotal}
                </Typography>
              )}
              <Typography variant="subtitle1">Deine XP-Aufschlüsselung:</Typography>
              {breakdown.length === 0 ? (
                <Typography color="text.secondary">Keine XP-Daten gefunden.</Typography>
              ) : (
                <ul style={{ marginTop: 8, marginBottom: 0 }}>
                  {breakdown.map((item) => (
                    <li key={item.actionType}>
                      {item.label}: <b>{item.xp} XP</b>
                    </li>
                  ))}
                </ul>
              )}
            </>
          )}
        </Box>
      </Box>
    </BaseModal>
  );
};
import getCroppedImg from '../utils/cropImage';
import Cropper from 'react-easy-crop';
import type { Area } from 'react-easy-crop';
import Avatar from '@mui/material/Avatar';
import EditIcon from '@mui/icons-material/Edit';
import UploadIcon from '@mui/icons-material/Upload';
import LinkIcon from '@mui/icons-material/Link';
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Divider from '@mui/material/Divider';
import MenuItem from '@mui/material/MenuItem';
import Alert from '@mui/material/Alert';
import Switch from '@mui/material/Switch';
import FormControlLabel from '@mui/material/FormControlLabel';
import Brightness4Icon from '@mui/icons-material/Brightness4';
import Brightness7Icon from '@mui/icons-material/Brightness7';
import BaseModal from './BaseModal';
import { apiJson } from '../utils/api';
import { useTheme } from '../context/ThemeContext';
import { useAuth } from '../context/AuthContext';
import { BACKEND_URL } from '../../config';
import { FaTrashAlt } from 'react-icons/fa';

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
  avatarUrl?: string;
}
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
  const { checkAuthStatus } = useAuth();
  const { mode, toggleTheme } = useTheme();
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
    confirmPassword: '',
    avatarUrl: '',
  });
  const [avatarFile, setAvatarFile] = React.useState<File | null>(null);
  const [dragActive, setDragActive] = React.useState(false);
  const dropJustHappened = React.useRef(false);
  const [crop, setCrop] = React.useState({ x: 0, y: 0 });
  const [zoom, setZoom] = React.useState(1);
  const [croppedAreaPixels, setCroppedAreaPixels] = React.useState<Area | null>(null);
  const [avatarModalOpen, setAvatarModalOpen] = React.useState(false);
  const [loading, setLoading] = React.useState(false);
  const [message, setMessage] = React.useState<{ text: string; type: 'success' | 'error' } | null>(null);
  const [relations, setRelations] = React.useState<UserRelation[]>([]);
  const [relationsOpen, setRelationsOpen] = React.useState(false);
  const [xpModalOpen, setXpModalOpen] = React.useState(false);

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

  // Titel/Level für Profil-Header
  const [profileTitle, setProfileTitle] = React.useState<string | null>(null);
  const [profileLevel, setProfileLevel] = React.useState<number | null>(null);
  const [profileXp, setProfileXp] = React.useState<number | null>(null);

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
        confirmPassword: '',
        avatarUrl: userData.avatarFile || '',
      });
      // Titel/Level extrahieren
      setProfileTitle(userData.title?.displayTitle?.displayName || null);
      setProfileLevel(userData.level?.level ?? null);
      setProfileXp(userData.level?.xpTotal ?? null);
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
      let avatarUrl = form.avatarUrl || '';
      if (avatarFile) {
        const data = new FormData();
        data.append('file', avatarFile);
        const uploadResp = await apiJson('/api/users/upload-avatar', {
          method: 'POST',
          body: data
        });
        if (uploadResp && uploadResp.url) {
          avatarUrl = uploadResp.url;
          setForm(prev => ({ ...prev, avatarUrl }));
          setAvatarFile(null);
        }
      }

      const updateData = {
        firstName: form.firstName,
        lastName: form.lastName,
        email: form.email,
        height: form.height,
        weight: form.weight,
        shoeSize: form.shoeSize,
        shirtSize: form.shirtSize,
        pantsSize: form.pantsSize,
        avatarUrl,
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
      setAvatarFile(null);
      
      if (onSave) {
        onSave({ ...form, avatarUrl });
      }
      
      await checkAuthStatus();
    } catch (error: any) {
      console.error('Fehler beim Aktualisieren des Profils:', error);
      setMessage({ text: error.message || 'Fehler beim Aktualisieren des Profils', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const removeAvatarPicture = async () => {
    if (!form.avatarUrl) return;

    try {
      await apiJson('/api/users/remove-avatar', { method: 'DELETE' });
      setForm(prev => ({ ...prev, avatarUrl: '' }));
      setAvatarFile(null);
      setMessage({ text: 'Avatar erfolgreich entfernt', type: 'success' });
      await checkAuthStatus();
    } catch (error) {
      console.error('Fehler beim Entfernen des Avatars:', error);
      setMessage({ text: 'Fehler beim Entfernen des Avatars', type: 'error' });
    }
  }

  const shirtSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
  const pantsSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '28/30', '30/30', '32/30', '34/30', '36/30', '28/32', '30/32', '32/32', '34/32', '36/32'];

  const titleWithLink = (
    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
      Profil bearbeiten
      <Tooltip title={relations.length > 0 ? 'Mit Profil(en) verknüpft' : 'Mit keinem Profil verknüpft'}>
        <span>
          <IconButton
            size="small"
            onClick={() => relations.length > 0 && setRelationsOpen(true)}
            sx={{ color: relations.length > 0 ? 'success.main' : 'grey.400' }}
            disabled={relations.length === 0}
          >
            <LinkIcon />
          </IconButton>
        </span>
      </Tooltip>
      {/* XP Breakdown IconButton */}
      <Tooltip title="XP-Aufschlüsselung anzeigen">
        <span>
          <IconButton
            size="small"
            onClick={() => setXpModalOpen(true)}
            sx={{ color: 'primary.main', ml: 0.5 }}
            aria-label="XP-Aufschlüsselung anzeigen"
          >
            <InfoOutlinedIcon fontSize="small" />
          </IconButton>
        </span>
      </Tooltip>
    </Box>
  );

  return (
    <>
      <BaseModal
        open={open}
        onClose={onClose}
        title={titleWithLink}
        maxWidth="md"
        actions={
          <>
            <Button onClick={onClose} variant="outlined" color="secondary">
              Abbrechen
            </Button>
            <Button 
              onClick={handleSave} 
              color="primary" 
              variant="contained"
              disabled={loading}
            >
              {loading ? 'Speichere...' : 'Speichern'}
            </Button>
          </>
        }
      >
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2, mt: 1 }}>
          <Box sx={{ position: 'relative', width: 72, height: 72, mb: 1 }}>
            <Avatar
              src={avatarFile ? URL.createObjectURL(avatarFile) : `${BACKEND_URL}/uploads/avatar/${form.avatarUrl}`}
              alt="Avatar"
              sx={{ width: 72, height: 72, fontSize: 32 }}
            />
            <IconButton
              size="small"
              sx={{
                position: 'absolute',
                bottom: -8,
                right: -8,
                bgcolor: 'background.paper',
                boxShadow: 1,
                opacity: 0.85,
                borderRadius: '50%',
                width: 32,
                height: 32,
                border: '1.5px solid',
                borderColor: 'grey.300',
                transition: 'opacity 0.2s',
                p: 0,
                minWidth: 0,
                '&:hover': { opacity: 1, bgcolor: 'primary.main', color: 'white' },
              }}
              onClick={() => setAvatarModalOpen(true)}
              aria-label="Avatar bearbeiten"
            >
              <EditIcon fontSize="small" />
            </IconButton>
            <IconButton
              size="small"
              sx={{
                position: 'absolute',
                top: -8,
                right: -8,
                bgcolor: 'background.paper',
                boxShadow: 1,
                opacity: 0.85,
                borderRadius: '50%',
                width: 32,
                height: 32,
                border: '1.5px solid',
                borderColor: 'grey.300',
                transition: 'opacity 0.2s',
                p: 0,
                minWidth: 0,
                '&:hover': { opacity: 1, bgcolor: 'primary.danger', color: 'white' },
              }}
              onClick={() => removeAvatarPicture()}
              aria-label="Avatar löschen"
            >
              <FaTrashAlt fontSize="small" />
            </IconButton>
          </Box>

          {/* Titel und Level unter dem Namen anzeigen */}
          {(profileTitle || profileLevel !== null) && (
            <Box sx={{ mb: 1 }}>
              {profileTitle && (
                <Typography variant="subtitle1" sx={{ fontWeight: 500 }}>
                  {profileTitle}
                </Typography>
              )}
              {profileLevel !== null && (
                <Typography variant="body2" color="text.secondary">
                  Level: {profileLevel}{profileXp !== null ? `  |  XP: ${profileXp}` : ''}
                </Typography>
              )}
            </Box>
          )}

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
        {/* Theme-Einstellung */}
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mb: 2 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            {mode === 'dark' ? <Brightness7Icon /> : <Brightness4Icon />}
            <Typography variant="subtitle1">Design</Typography>
          </Box>
          <FormControlLabel
            control={
              <Switch
                checked={mode === 'dark'}
                onChange={toggleTheme}
                color="primary"
              />
            }
            label={mode === 'dark' ? 'Dark Mode' : 'Light Mode'}
          />
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
          <Alert severity={message.type}>
            {message.text}
          </Alert>
        )}
      </Box>
    </BaseModal>
    {/* XP Breakdown Modal */}
    <XpBreakdownModal open={xpModalOpen} onClose={() => setXpModalOpen(false)} />
    
    {/* AvatarModal für Upload/URL */}
    <BaseModal
      open={avatarModalOpen}
      onClose={() => setAvatarModalOpen(false)}
      title="Profilbild ändern"
      maxWidth="xs"
      actions={
        <>
          <Button onClick={() => setAvatarModalOpen(false)} variant="outlined">Abbrechen</Button>
          <Button
            onClick={async () => {
              if (avatarFile && croppedAreaPixels) {
                // Cropping durchführen
                const cropped = await getCroppedImg(URL.createObjectURL(avatarFile), croppedAreaPixels);
                if (cropped) {
                  // DataURL in File umwandeln
                  const arr = cropped.split(',');
                  const match = arr[0].match(/:(.*?);/);
                  const mime = match ? match[1] : '';
                  const bstr = atob(arr[1]);
                  let n = bstr.length;
                  const u8arr = new Uint8Array(n);
                  while (n--) u8arr[n] = bstr.charCodeAt(n);
                  const file = new File([u8arr], 'avatar.png', { type: mime });
                  setAvatarFile(file);
                }
              }
              setAvatarModalOpen(false);
            }}
            variant="contained"
            disabled={!(avatarFile || form.avatarUrl)}
          >
            Übernehmen
          </Button>
        </>
      }
    >
      <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2, mt: 1 }}>
        {/* Drag & Drop Target */}
        <Box sx={{ position: 'relative', width: 240, mb: 2, display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
          {/* Avatar-Rahmen mit Drag & Drop und Klick, nur EIN Avatar/Cropper */}
          <Box
            // Kein onClick mehr am Rahmen, nur noch Drag&Drop
            onDragOver={e => { e.preventDefault(); e.stopPropagation(); setDragActive(true); }}
            onDragEnter={e => { e.preventDefault(); e.stopPropagation(); setDragActive(true); }}
            onDragLeave={e => { e.preventDefault(); e.stopPropagation(); setDragActive(false); }}
            onDrop={e => {
              e.preventDefault();
              e.stopPropagation();
              setDragActive(false);
              if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                setAvatarFile(e.dataTransfer.files[0]);
                setForm(prev => ({ ...prev, avatarUrl: '' }));
                dropJustHappened.current = true;
                setTimeout(() => { dropJustHappened.current = false; }, 100);
              }
            }}
            sx={{
              width: 220,
              height: 220,
              border: dragActive ? '3px solid #1976d2' : '2px dashed',
              borderColor: dragActive ? '#1976d2' : 'grey.400',
              borderRadius: '50%',
              boxShadow: dragActive ? '0 0 0 4px #90caf9' : '0 0 16px 0 rgba(0,0,0,0.10)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              bgcolor: dragActive ? '#e3f2fd' : '#fafbfc',
              position: 'relative',
              overflow: 'hidden',
              transition: 'border-color 0.2s, box-shadow 0.2s, background 0.2s',
              cursor: 'pointer',
              '&:hover': { borderColor: 'primary.main', bgcolor: '#f0f4fa' },
            }}
          >
            {/* Avatar oder Cropper, aber bei Drag pointerEvents: 'none', damit Drop immer auf dem Kreis ankommt */}
            {avatarFile ? (
              <Box sx={{ width: '100%', height: '100%', pointerEvents: dragActive ? 'none' : 'auto' }}>
                <Cropper
//                  image={`${BACKEND_URL}/uploads/avatar/${avatarFile} `}
                  image={URL.createObjectURL(avatarFile)}
                  crop={crop}
                  zoom={zoom}
                  aspect={1}
                  cropShape="round"
                  showGrid={false}
                  onCropChange={setCrop}
                  onZoomChange={setZoom}
                  onCropComplete={(_, croppedAreaPixels) => setCroppedAreaPixels(croppedAreaPixels)}
                  style={{ containerStyle: { width: '100%', height: '100%' } }}
                />
              </Box>
            ) : (
              <Avatar
                src={`${BACKEND_URL}/uploads/avatar/${form.avatarUrl}`}
                alt="Avatar"
                sx={{ width: 120, height: 120, mx: 'auto', pointerEvents: dragActive ? 'none' : 'auto' }}
              />
            )}
            <input
              id="avatar-upload-input"
              type="file"
              accept="image/*"
              hidden
              onChange={e => {
                if (e.target.files && e.target.files[0]) {
                  setAvatarFile(e.target.files[0]);
                  setForm(prev => ({ ...prev, avatarUrl: '' }));
                }
              }}
            />
            {/* Hinweistext nur im Rahmen */}
            <Box sx={{
              position: 'absolute',
              bottom: 20,
              left: 12,
              right: 12,
              textAlign: 'center',
              fontSize: 13,
              color: '#fff',
              fontWeight: 'bold',
              textShadow: '0 2px 8px rgba(0,0,0,0.85), 0 0px 2px #000',
              letterSpacing: 0.2,
              userSelect: 'none',
              pointerEvents: 'none',
              whiteSpace: 'normal',
              lineHeight: 1.3,
            }}>
              Bild hierher ziehen, um ein neues Bild zu wählen
            </Box>
          </Box>
          {/* Upload-Button unterhalb, klar beschriftet, KEIN weiterer Avatar/Text darunter */}
          <Button
            variant="outlined"
            startIcon={<UploadIcon />}
            sx={{ mt: 2, borderRadius: 2, fontWeight: 500, textTransform: 'none', minWidth: 160 }}
            onClick={() => document.getElementById('avatar-upload-input')?.click()}
          >
            Bild auswählen
          </Button>
        </Box>
        <TextField
          label="Avatar-URL"
          value={form.avatarUrl}
          onChange={e => {
            setForm(prev => ({ ...prev, avatarUrl: e.target.value }));
            setAvatarFile(null);
          }}
          fullWidth
          sx={{ mt: 2, maxWidth: 320, alignSelf: 'center' }}
        />
      </Box>
    </BaseModal>
    
    {/* Modal für UserRelation-Übersicht */}
    <BaseModal
      open={relationsOpen}
      onClose={() => setRelationsOpen(false)}
      title="Verknüpfte Profile"
      maxWidth="sm"
      actions={
        <Button onClick={() => setRelationsOpen(false)} variant="contained">
          Schließen
        </Button>
      }
    >
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
    </BaseModal>
  </>
  );
};

export default ProfileModal;
