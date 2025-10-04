import React, { useState, useEffect } from 'react';
import {
  Button,
  TextField,
  MenuItem,
  Box
} from '@mui/material';
import { Video, VideoType, Camera } from '../services/videos';
import BaseModal from './BaseModal';

interface VideoDialogProps {
  open: boolean;
  onClose: () => void;
  onSave: (data: any) => void;
  videoTypes: VideoType[];
  cameras: Camera[];
  initialData?: Partial<Video>;
  loading?: boolean;
}

export default function VideoModal({ open, onClose, onSave, videoTypes, cameras, initialData = {}, loading }: VideoDialogProps) {
  const [form, setForm] = useState({
    video_id: initialData.id || '',
    name: initialData.name || '',
    url: initialData.url || '',
    filePath: initialData.filePath || '',
    gameStart: initialData.gameStart || '',
    sort: initialData.sort || '',
    videoType: initialData.videoType?.id ? initialData.videoType.id : '',
    camera: initialData.camera?.id ? initialData.camera.id : '',
    length: initialData.length || ''
  });

  useEffect(() => {
    setForm({
      video_id: initialData.id || '',
      name: initialData.name || '',
      url: initialData.url || '',
      filePath: initialData.filePath || '',
      gameStart: initialData.gameStart || '',
      sort: initialData.sort || '',
      videoType: initialData.videoType?.id ? initialData.videoType.id : '',
      camera: initialData.camera?.id ? initialData.camera.id : '',
      length: initialData.length || ''
    });
  }, [open, initialData?.id]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSave(form);
  };

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      title={form.video_id ? 'Video bearbeiten' : 'Video hinzufügen'}
      maxWidth="sm"
      actions={
        <>
          <Button onClick={onClose} color="secondary" variant="outlined">
            Abbrechen
          </Button>
          <Button type="submit" variant="contained" color="primary" disabled={loading} onClick={handleSubmit}>
            {form.video_id ? 'Speichern' : 'Hinzufügen'}
          </Button>
        </>
      }
    >
      <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
        <TextField
          label="Name"
          name="name"
          value={form.name}
          onChange={handleChange}
          required
          fullWidth
          autoFocus
        />
        <TextField
          label="YouTube-Link oder Datei-URL"
          name="url"
          value={form.url}
          onChange={handleChange}
          fullWidth
        />
        <TextField
          label="Dateipfad (optional)"
          name="filePath"
          value={form.filePath}
          onChange={handleChange}
          fullWidth
        />
        <Box sx={{ display: 'flex', gap: 2 }}>
          <TextField
            label="Startzeit (Sek.)"
            name="gameStart"
            value={form.gameStart}
            onChange={handleChange}
            type="number"
            fullWidth
          />
          <TextField
            label="Sortierung"
            name="sort"
            value={form.sort}
            onChange={handleChange}
            type="number"
            fullWidth
          />
        </Box>
        <Box sx={{ display: 'flex', gap: 2 }}>
          <TextField
            select
            label="Video-Typ"
            name="videoType"
            value={form.videoType}
            onChange={handleChange}
            required
            fullWidth
          >
            <MenuItem value="">Keine</MenuItem>
            {videoTypes.map((type) => (
              <MenuItem key={type.id} value={String(type.id)}>{type.name}</MenuItem>
            ))}
          </TextField>
          <TextField
            select
            label="Kamera"
            name="camera"
            value={form.camera}
            onChange={handleChange}
            fullWidth
          >
            <MenuItem value="">Keine</MenuItem>
            {cameras.map((cam) => (
              <MenuItem key={cam.id} value={String(cam.id)}>{cam.name}</MenuItem>
            ))}
          </TextField>
        </Box>
        <TextField
          label="Länge (Sek.)"
          name="length"
          value={form.length}
          onChange={handleChange}
          type="number"
          fullWidth
        />
      </Box>
    </BaseModal>
  );
}
