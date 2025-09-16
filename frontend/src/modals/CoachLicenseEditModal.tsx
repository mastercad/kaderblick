import React, { useEffect, useState } from 'react';
import {
    Dialog, DialogTitle, DialogContent, Button, Box, TextField, CircularProgress, Alert, Divider, IconButton
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import { CoachLicense } from '../types/coachLicense';
import { apiJson } from '../utils/api';

interface CoachLicenseEditModalProps {
    openCoachLicenseEditModal: boolean;
    coachLicenseId: number | null;
    onCoachLicenseEditModalClose: () => void;
    onCoachLicenseSaved?: (coachLicense: CoachLicense) => void;
}

const CoachLicenseEditModal: React.FC<CoachLicenseEditModalProps> = ({ openCoachLicenseEditModal, coachLicenseId, onCoachLicenseEditModalClose, onCoachLicenseSaved }) => {
    const [coachLicense, setCoachLicense] = useState<CoachLicense | null>(null);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (openCoachLicenseEditModal && coachLicenseId) {
            setLoading(true);
            apiJson<{ coachLicense: CoachLicense }>(`/api/coach-licenses/${coachLicenseId}`)
                .then(data => {
                    setCoachLicense(data.coachLicense);
                    setLoading(false);
                })
                .catch(() => {
                    setError('Fehler beim Laden der Lizenzdaten.');
                    setLoading(false);
                });
        } else if (openCoachLicenseEditModal) {
            setCoachLicense(null);
        }
    }, [openCoachLicenseEditModal, coachLicenseId]);

    const handleCoachLicenseEditChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value, type, checked } = e.target;
        setCoachLicense((prev: any) => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleCoachLicenseEditSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        try {
          const url = coachLicense?.id ? `/api/coach-licenses/${coachLicense.id}` : '/api/coach-licenses';
          const method = coachLicense?.id ? 'PUT' : 'POST';
          const res = await apiJson(url, {
            method,
            body: coachLicense,
            headers: { 'Content-Type': 'application/json' },
          });

          if (onCoachLicenseSaved) onCoachLicenseSaved(res.coachLicense || res.data || coachLicense);
          onCoachLicenseEditModalClose();
        } catch (err: any) {
          setError(err?.message || 'Fehler beim Speichern');
        } finally {
          setLoading(false);
        }
    };

    return (
        <Dialog open={openCoachLicenseEditModal} onClose={onCoachLicenseEditModalClose} maxWidth="xs" fullWidth>
            <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', pr: 2 }}>
                Trainerlizenz bearbeiten
                <IconButton aria-label="close" onClick={onCoachLicenseEditModalClose} size="small" sx={{ ml: 2 }}>
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
                    <form id="coachLicenseEditForm" autoComplete="off" onSubmit={handleCoachLicenseEditSubmit}>
                        <input type="hidden" name="id" value={coachLicense?.id} />
                        <Box sx={{ bgcolor: 'background.default', p: 0 }}>
                            <Box mb={2}>
                                <TextField label="Name" name="name" value={coachLicense?.name || ''} onChange={handleCoachLicenseEditChange} required fullWidth margin="normal" />
                                <TextField label="Beschreibung" name="description" value={coachLicense?.description || ''} onChange={handleCoachLicenseEditChange} fullWidth margin="normal" />
                                <TextField label="Länder Code" name="countryCode" value={coachLicense?.countryCode || ''} onChange={handleCoachLicenseEditChange} fullWidth margin="normal" />
                            </Box>
                        </Box>
                        <Box display="flex" justifyContent="flex-end" gap={2} mt={3} mb={1}>
                            <Button onClick={onCoachLicenseEditModalClose} variant="outlined" color="secondary">
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

export default CoachLicenseEditModal;
