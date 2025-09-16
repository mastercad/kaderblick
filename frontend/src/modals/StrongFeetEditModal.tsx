import React, { useEffect, useState } from 'react';
import {
    Dialog, DialogTitle, DialogContent, Button, Box, TextField, CircularProgress, Alert, Divider, IconButton
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import { StrongFeet } from '../types/strongFeet';
import { apiJson } from '../utils/api';

interface StrongFeetEditModalProps {
    openStrongFeetEditModal: boolean;
    strongFeetId: number | null;
    onStrongFeetEditModalClose: () => void;
    onStrongFeetSaved?: (strongFeet: StrongFeet) => void;
}

const StrongFeetEditModal: React.FC<StrongFeetEditModalProps> = ({ openStrongFeetEditModal, strongFeetId, onStrongFeetEditModalClose, onStrongFeetSaved }) => {
    const [strongFeet, setStrongFeet] = useState<StrongFeet | null>(null);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (openStrongFeetEditModal && strongFeetId) {
            setLoading(true);
            apiJson<{ strongFeet: StrongFeet }>(`/api/strong-feet/${strongFeetId}`)
                .then(data => {
                    setStrongFeet(data.strongFeet);
                    setLoading(false);
                })
                .catch(() => {
                    setError('Fehler beim Laden der Positionsdaten.');
                    setLoading(false);
                });
        } else if (openStrongFeetEditModal) {
            setStrongFeet(null);
        }
    }, [openStrongFeetEditModal, strongFeetId]);

    const handleStrongFeetEditChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value, type, checked } = e.target;
        setStrongFeet((prev: any) => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleStrongFeetEditSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        try {
          const url = strongFeet.id ? `/api/strong-feet/${strongFeet.id}` : '/api/strong-feet';
          const method = strongFeet.id ? 'PUT' : 'POST';
          const res = await apiJson(url, {
            method,
            body: strongFeet,
            headers: { 'Content-Type': 'application/json' },
          });

          if (onStrongFeetSaved) onStrongFeetSaved(res.strongFeet || res.data || strongFeet);
          onStrongFeetEditModalClose();
        } catch (err: any) {
          setError(err?.message || 'Fehler beim Speichern');
        } finally {
          setLoading(false);
        }
    };

    return (
        <Dialog open={openStrongFeetEditModal} onClose={onStrongFeetEditModalClose} maxWidth="xs" fullWidth>
            <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', pr: 2 }}>
                Starken Fuß bearbeiten
                <IconButton aria-label="close" onClick={onStrongFeetEditModalClose} size="small" sx={{ ml: 2 }}>
                    <CloseIcon />
                </IconButton>
            </DialogTitle>
            <DialogContent>
                {loading ? (
                    <Box display="flex" alignItems="center" justifyContent="center" minHeight={200}>
                        <CircularProgress />
                    </Box>
                ) : error ? (
                    <Alert severity="error">{error}</Alert>
                ) : (
                    <form id="strongFeetEditForm" autoComplete="off" onSubmit={handleStrongFeetEditSubmit}>
                        <input type="hidden" name="id" value={strongFeet?.id} />
                        <Box sx={{ bgcolor: 'background.default', p: 0 }}>
                            <Box mb={2}>
                                <TextField label="Name" name="name" value={strongFeet?.name || ''} onChange={handleStrongFeetEditChange} required fullWidth margin="normal" />
                                <TextField label="Kurzname" name="code" value={strongFeet?.code || ''} onChange={handleStrongFeetEditChange} fullWidth margin="normal" />
                            </Box>
                        </Box>
                        <Box display="flex" justifyContent="flex-end" gap={2} mt={3} mb={1}>
                            <Button onClick={onStrongFeetEditModalClose} variant="outlined" color="secondary">
                                Abbrechen
                            </Button>
                            <Button type="submit" variant="contained" color="primary" disabled={saving}>
                                {saving ? <CircularProgress size={20} /> : 'Speichern'}
                            </Button>
                        </Box>
                    </form>
                )}
            </DialogContent>
        </Dialog>
    );
};

export default StrongFeetEditModal;
