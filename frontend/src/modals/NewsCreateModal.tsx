import React, { useState } from 'react';
import {
  Button, TextField, MenuItem, Select, InputLabel, FormControl, Alert
} from '@mui/material';
import { apiJson } from '../utils/api';
import BaseModal from './BaseModal';

interface Club { id: number; name: string; }
interface Team { id: number; name: string; }
interface VisibilityOption { label: string; value: string; }

interface Props {
  open: boolean;
  onClose: () => void;
  onSuccess: () => void;
  clubs: Club[];
  teams: Team[];
  visibilityOptions: VisibilityOption[];
}

const NewsCreateModal: React.FC<Props> = ({ open, onClose, onSuccess, clubs, teams, visibilityOptions }) => {
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [visibility, setVisibility] = useState('platform');
  const [clubId, setClubId] = useState('');
  const [teamId, setTeamId] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const reset = () => {
    setTitle('');
    setContent('');
    setVisibility('platform');
    setClubId('');
    setTeamId('');
    setError(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const payload: any = { title, content, visibility };
      if (visibility === 'club') payload.club_id = clubId;
      if (visibility === 'team') payload.team_id = teamId;
      const res = await apiJson('/news/create', { method: 'POST', body: payload });
      const data = res as { success?: boolean; error?: string };
      if (data.success) {
        reset();
        onSuccess();
        onClose();
      } else {
        setError(data.error || 'Fehler beim Senden');
      }
    } catch (e) {
      setError('Fehler beim Senden');
    } finally {
      setLoading(false);
    }
  };

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      maxWidth="sm"
      title="News erstellen"
      actions={
        <>
          <Button onClick={onClose} variant="outlined" color="secondary" disabled={loading}>Abbrechen</Button>
          <Button type="submit" form="newsCreateForm" variant="contained" color="primary" disabled={loading}>Senden</Button>
        </>
      }
    >
      <form id="newsCreateForm" onSubmit={handleSubmit}>
        {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
        <TextField
          label="Titel"
          value={title}
          onChange={e => setTitle(e.target.value)}
          fullWidth
          required
          sx={{ mt: 1 }}
        />
        <TextField
          label="Inhalt"
          value={content}
          onChange={e => setContent(e.target.value)}
          fullWidth
          required
          margin="normal"
          multiline
          minRows={4}
        />
        <FormControl fullWidth margin="normal" required>
          <InputLabel id="visibility-label">Sichtbarkeit</InputLabel>
          <Select
            labelId="visibility-label"
            value={visibility}
            label="Sichtbarkeit"
            onChange={e => setVisibility(e.target.value)}
          >
            {visibilityOptions.map(opt => (
              <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
            ))}
          </Select>
        </FormControl>
        {visibility === 'club' && (
          <FormControl fullWidth margin="normal" required>
            <InputLabel id="club-label">Verein</InputLabel>
            <Select
              labelId="club-label"
              value={clubId}
              label="Verein"
              onChange={e => setClubId(e.target.value)}
            >
              <MenuItem value="">Bitte wählen...</MenuItem>
              {clubs.map(club => (
                <MenuItem key={club.id} value={club.id}>{club.name}</MenuItem>
              ))}
            </Select>
          </FormControl>
        )}
        {visibility === 'team' && (
          <FormControl fullWidth margin="normal" required>
            <InputLabel id="team-label">Team</InputLabel>
            <Select
              labelId="team-label"
              value={teamId}
              label="Team"
              onChange={e => setTeamId(e.target.value)}
            >
              <MenuItem value="">Bitte wählen...</MenuItem>
              {teams.map(team => (
                <MenuItem key={team.id} value={team.id}>{team.name}</MenuItem>
              ))}
            </Select>
          </FormControl>
        )}
      </form>
    </BaseModal>
  );
};

export default NewsCreateModal;
