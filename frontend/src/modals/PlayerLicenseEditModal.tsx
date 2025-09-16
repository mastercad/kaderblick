import React, { useEffect, useState } from 'react';
import {
    Dialog, DialogTitle, DialogContent, Button, Box, TextField, CircularProgress, Alert, Divider, IconButton
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import { PlayerLicense } from '../types/playerLicense';
import { apiJson } from '../utils/api';

interface PlayerLicenseEditModalProps {
    openPlayerLicenseEditModal: boolean;
    playerLicenseId: number | null;
    onPlayerLicenseEditModalClose: () => void;
    onPlayerLicenseSaved?: (playerLicense: PlayerLicense) => void;
}

const PlayerLicenseEditModal: React.FC<PlayerLicenseEditModalProps> = ({ openPlayerLicenseEditModal, playerLicenseId, onPlayerLicenseEditModalClose, onPlayerLicenseSaved }) => {
    const [playerLicense, setPlayerLicense] = useState<PlayerLicense | null>(null);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (openPlayerLicenseEditModal && playerLicenseId) {
            setLoading(true);
            apiJson<{ playerLicense: PlayerLicense }>(`/api/player-licenses/${playerLicenseId}`)
                .then(data => {
                    setPlayerLicense(data.playerLicense);
                    setLoading(false);
                })
                .catch(() => {
                    setError('Fehler beim Laden der Lizenzdaten.');
                    setLoading(false);
                });
        } else if (openPlayerLicenseEditModal) {
            setPlayerLicense(null);
        }
    }, [openPlayerLicenseEditModal, playerLicenseId]);

    const handlePlayerLicenseEditChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value, type, checked } = e.target;
        setPlayerLicense((prev: any) => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handlePlayerLicenseEditSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        try {
          const url = playerLicense?.id ? `/api/player-licenses/${playerLicense.id}` : '/api/player-licenses';
          const method = playerLicense?.id ? 'PUT' : 'POST';
          const res = await apiJson(url, {
            method,
            body: playerLicense,
            headers: { 'Content-Type': 'application/json' },
          });

          if (onPlayerLicenseSaved) onPlayerLicenseSaved(res.playerLicense || res.data || playerLicense);
          onPlayerLicenseEditModalClose();
        } catch (err: any) {
          setError(err?.message || 'Fehler beim Speichern');
        } finally {
          setLoading(false);
        }
    };

    return (
        <Dialog open={openPlayerLicenseEditModal} onClose={onPlayerLicenseEditModalClose} maxWidth="xs" fullWidth>
            <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', pr: 2 }}>
                Trainerlizenz bearbeiten
                <IconButton aria-label="close" onClick={onPlayerLicenseEditModalClose} size="small" sx={{ ml: 2 }}>
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
                    <form id="playerLicenseEditForm" autoComplete="off" onSubmit={handlePlayerLicenseEditSubmit}>
                        <input type="hidden" name="id" value={playerLicense?.id} />
                        <Box sx={{ bgcolor: 'background.default', p: 0 }}>
                            <Box mb={2}>
                                <TextField label="Name" name="name" value={playerLicense?.name || ''} onChange={handlePlayerLicenseEditChange} required fullWidth margin="normal" />
                                <TextField label="Beschreibung" name="description" value={playerLicense?.description || ''} onChange={handlePlayerLicenseEditChange} fullWidth margin="normal" />
                                <TextField label="Länder Code" name="countryCode" value={playerLicense?.countryCode || ''} onChange={handlePlayerLicenseEditChange} fullWidth margin="normal" />
                            </Box>
                        </Box>
                        <Box display="flex" justifyContent="flex-end" gap={2} mt={3} mb={1}>
                            <Button onClick={onPlayerLicenseEditModalClose} variant="outlined" color="secondary">
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

export default PlayerLicenseEditModal;
