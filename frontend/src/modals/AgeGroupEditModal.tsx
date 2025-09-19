import React, { useEffect, useState } from 'react';
import {
    Dialog, DialogTitle, DialogContent, Button, Box, TextField, CircularProgress, Alert, Divider, IconButton
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import { AgeGroup } from '../types/ageGroup';
import { apiJson } from '../utils/api';

interface AgeGroupEditModalProps {
    openAgeGroupEditModal: boolean;
    ageGroupId: number | null;
    onAgeGroupEditModalClose: () => void;
    onAgeGroupSaved?: (ageGroup: AgeGroup) => void;
}

const AgeGroupEditModal: React.FC<AgeGroupEditModalProps> = ({ openAgeGroupEditModal, ageGroupId, onAgeGroupEditModalClose, onAgeGroupSaved }) => {
    const [ageGroup, setAgeGroup] = useState<any>(null);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (openAgeGroupEditModal && ageGroupId) {
            setLoading(true);
            apiJson<{ ageGroup: AgeGroup }>(`/api/age-groups/${ageGroupId}`)
                .then(data => {
                    setAgeGroup(data.ageGroup);
                    setLoading(false);
                })
                .catch(() => {
                    setError('Fehler beim Laden der Vereinsdaten.');
                    setLoading(false);
                });
        } else if (openAgeGroupEditModal) {
            setAgeGroup(null);
        }
    }, [openAgeGroupEditModal, ageGroupId]);

    const handleAgeGroupEditChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value, type, checked } = e.target;
        setAgeGroup((prev: any) => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleAgeGroupEditSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        try {
          const url = ageGroup.id ? `/api/age-groups/${ageGroup.id}` : '/api/age-groups';
          const method = ageGroup.id ? 'PUT' : 'POST';
          const res = await apiJson(url, {
            method,
            body: ageGroup,
            headers: { 'Content-Type': 'application/json' },
          });

          if (onAgeGroupSaved) onAgeGroupSaved(res.ageGroup || res.data || ageGroup);
          onAgeGroupEditModalClose();
        } catch (err: any) {
          setError(err?.message || 'Fehler beim Speichern');
        } finally {
          setLoading(false);
        }
    };

    return (
        <Dialog open={openAgeGroupEditModal} onClose={onAgeGroupEditModalClose} maxWidth="sm" fullWidth>
            <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', pr: 2 }}>
                Altersgruppe bearbeiten
                <IconButton aria-label="close" onClick={onAgeGroupEditModalClose} size="small" sx={{ ml: 2 }}>
                    <CloseIcon />
                </IconButton>
            </DialogTitle>
            <DialogContent>
                {loading ? (
                    <Box display="flex" alignItems="center" justifyContent="center" minHeight={200}>
                        <CircularProgress />
                    </Box>
                ) : (
                    <>
                        {error && (
                            <Alert severity="error" sx={{ mb: 2, fontWeight: 'bold', fontSize: '1.1em' }}>
                                {error}
                            </Alert>
                        )}
                        <form id="ageGroupEditForm" autoComplete="off" onSubmit={handleAgeGroupEditSubmit}>
                            <input type="hidden" name="id" value={ageGroup?.id} />
                            <Box sx={{ bgcolor: 'background.default', p: 0 }}>
                                <Box mb={2}>
                                    <TextField label="Name" name="name" value={ageGroup?.name || ''} onChange={handleAgeGroupEditChange} required fullWidth margin="normal" />
                                    <TextField label="Englischer Name" name="englishName" value={ageGroup?.englishName || ''} onChange={handleAgeGroupEditChange} fullWidth margin="normal" />
                                    <TextField label="Code" name="code" value={ageGroup?.code || ''} onChange={handleAgeGroupEditChange} fullWidth margin="normal" />
                                    <TextField label="Beschreibung" name="description" value={ageGroup?.description || ''} onChange={handleAgeGroupEditChange} fullWidth margin="normal" multiline minRows={2} />
                                </Box>
                                <Divider sx={{ mb: 2 }} />
                                <Box display="grid" gridTemplateColumns="1fr 1fr" gap={2}>
                                    <TextField label="Mindestalter" name="minAge" type="number" value={ageGroup?.minAge ?? ''} onChange={handleAgeGroupEditChange} fullWidth margin="normal" inputProps={{ min: 0 }} />
                                    <TextField label="Höchstalter" name="maxAge" type="number" value={ageGroup?.maxAge ?? ''} onChange={handleAgeGroupEditChange} fullWidth margin="normal" inputProps={{ min: 0 }} />
                                    <TextField label="Stichtag (MM-TT)" name="referenceDate" value={ageGroup?.referenceDate || ''} onChange={handleAgeGroupEditChange} fullWidth margin="normal" placeholder="01-01" />
                                </Box>
                            </Box>
                            <Box display="flex" justifyContent="flex-end" gap={2} mt={3} mb={1}>
                                <Button onClick={onAgeGroupEditModalClose} variant="outlined" color="secondary">
                                    Abbrechen
                                </Button>
                                <Button type="submit" variant="contained" color="primary" disabled={saving}>
                                    {saving ? <CircularProgress size={20} /> : 'Speichern'}
                                </Button>
                            </Box>
                        </form>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
};

export default AgeGroupEditModal;
